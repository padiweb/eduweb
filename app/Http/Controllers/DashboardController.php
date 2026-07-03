<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return match ($user->role) {
            'admin'                  => redirect()->route('admin.dashboard'),
            'guru', 'wali_kelas'     => redirect()->route('guru.dashboard'),
            'kesiswaan'              => redirect()->route('kesiswaan.dashboard'),
            'siswa'                  => redirect()->route('siswa.siswa.dashboard'),
            default                  => redirect()->route('login'),
        };
    }

    public function admin()
    {
        $school = auth()->user()->school;

        $stats = [
            'siswa'        => User::where('school_id', $school->id)->where('role', 'siswa')->count(),
            'guru'         => User::where('school_id', $school->id)->whereIn('role', ['guru', 'wali_kelas'])->count(),
            'kelas'        => Classroom::where('school_id', $school->id)
                                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                                ->count(),
            'sesi_hari_ini'=> AttendanceSession::where('school_id', $school->id)
                                ->whereDate('session_date', today())
                                ->count(),
        ];

        $recentSessions = AttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->with(['classroom', 'openedBy', 'attendances'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentSessions'));
    }

    public function guru()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;

        // Ambil kelas yang ada di sekolah
        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with('major')
            ->get();

        // Sesi hari ini
        $todaySessions = AttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->with(['classroom', 'attendances'])
            ->get();

        $stats = [
            'total_kelas'   => $classrooms->count(),
            'sesi_aktif'    => $todaySessions->where('is_closed', false)->count(),
            'total_hadir'   => $todaySessions->flatMap->attendances->whereIn('status', ['hadir', 'terlambat'])->count(),
            'total_alfa'    => $todaySessions->flatMap->attendances->where('status', 'alfa')->count(),
        ];

        return view('dashboard.guru', compact('stats', 'todaySessions', 'classrooms'));
    }

    public function kesiswaan()
    {
        $school = auth()->user()->school;

        $stats = [
            'pelanggaran_bulan_ini' => \App\Models\Violation::where('school_id', $school->id)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'alfa_hari_ini' => \App\Models\Attendance::where('school_id', $school->id)
                ->where('status', 'alfa')
                ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))
                ->count(),
            'terlambat_hari_ini' => \App\Models\Attendance::where('school_id', $school->id)
                ->where('status', 'terlambat')
                ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))
                ->count(),
        ];

        return view('dashboard.kesiswaan', compact('stats'));
    }

    public function siswa()
    {
        $student = auth()->user();
        $school  = $student->school;

        // Cek absensi hari ini
        $todayAttendance = \App\Models\Attendance::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))
            ->first();

        // Rekap bulan ini
        $monthStats = \App\Models\Attendance::where('student_id', $student->id)
            ->whereHas('session', fn($q) =>
                $q->whereMonth('session_date', now()->month)
                  ->whereYear('session_date', now()->year)
            )
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $hadirCount = ($monthStats['hadir'] ?? 0) + ($monthStats['terlambat'] ?? 0);
        $totalCount = array_sum($monthStats);
        $rate       = $totalCount > 0 ? round(($hadirCount / $totalCount) * 100, 1) : 0;

        // Total poin pelanggaran
        $violationPoints = \App\Models\Violation::where('student_id', $student->id)
            ->where('is_archived', false)
            ->sum('points');

        return view('dashboard.siswa', compact(
            'todayAttendance', 'monthStats', 'rate', 'violationPoints'
        ));
    }
}
