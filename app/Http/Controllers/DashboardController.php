<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\PaymentBill;
use App\Models\PaymentTransaction;
use App\Models\PrakerinPlacement;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Violation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return match ($user->role) {
            'admin'          => redirect()->route('admin.dashboard'),
            'guru','wali_kelas' => redirect()->route('guru.dashboard'),
            'kesiswaan'      => redirect()->route('kesiswaan.dashboard'),
            'siswa'          => redirect()->route('siswa.siswa.dashboard'),
            'bendahara'      => redirect()->route('bendahara.dashboard'),
            'kepala_sekolah' => redirect()->route('kepala.dashboard'),
            default          => abort(403, 'Role tidak dikenali.'),
        };
    }

    public function admin()
    {
        $school = auth()->user()->school;

        $stats = [
            'siswa'         => User::where('school_id', $school->id)->where('role', 'siswa')->where('is_active', true)->count(),
            'guru'          => User::where('school_id', $school->id)->whereIn('role', ['guru', 'wali_kelas'])->where('is_active', true)->count(),
            'kelas'         => Classroom::where('school_id', $school->id)->whereHas('academicYear', fn($q) => $q->where('is_active', true))->count(),
            'hadir_hari_ini'=> Attendance::where('school_id', $school->id)->whereIn('status', ['hadir','terlambat'])
                                ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))->count(),
            'alfa_hari_ini' => Attendance::where('school_id', $school->id)->where('status', 'alfa')
                                ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))->count(),
            'tunggakan'     => PaymentBill::where('school_id', $school->id)->whereIn('status', ['unpaid','partial'])->count(),
        ];

        $recentSessions = AttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->with(['classroom', 'openedBy', 'attendances'])
            ->latest()->take(10)->get();

        // Rekap kehadiran 7 hari
        $attendanceChart = collect(range(6, 0))->map(function($daysAgo) use ($school) {
            $date = today()->subDays($daysAgo);
            $total = Attendance::where('school_id', $school->id)
                ->whereHas('session', fn($q) => $q->whereDate('session_date', $date))->count();
            $hadir = Attendance::where('school_id', $school->id)
                ->whereIn('status', ['hadir','terlambat'])
                ->whereHas('session', fn($q) => $q->whereDate('session_date', $date))->count();
            return ['date' => $date->format('d/m'), 'hadir' => $hadir, 'total' => $total];
        });

        return view('dashboard.admin', compact('stats', 'recentSessions', 'attendanceChart'));
    }

    public function guru()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;

        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with(['major','students'])->get();

        $todaySessions = AttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->with(['classroom','attendances'])->get();

        $stats = [
            'total_kelas'  => $classrooms->count(),
            'sesi_aktif'   => $todaySessions->where('is_closed', false)->count(),
            'total_hadir'  => $todaySessions->flatMap->attendances->whereIn('status', ['hadir','terlambat'])->count(),
            'total_alfa'   => $todaySessions->flatMap->attendances->where('status', 'alfa')->count(),
            'total_siswa'  => $classrooms->sum(fn($c) => $c->students->count()),
        ];

        // Jurnal mengajar bulan ini
        $jurnalBulanIni = \App\Models\TeachingJournal::where('teacher_id', $teacher->id)
            ->whereMonth('journal_date', now()->month)->count();

        // Prakerin koordinator - pivot pakai teacher_id
        $prakerinCount = PrakerinPlacement::whereHas('location', fn($q) =>
            $q->whereHas('supervisors', fn($q2) => $q2->where('prakerin_loc_supervisors.teacher_id', $teacher->id))
        )->where('is_active', true)->count();

        return view('dashboard.guru', compact(
            'stats', 'todaySessions', 'classrooms', 'jurnalBulanIni', 'prakerinCount'
        ));
    }

    public function kesiswaan()
    {
        $school = auth()->user()->school;

        $stats = [
            'pelanggaran_bulan_ini' => Violation::where('school_id', $school->id)->whereMonth('created_at', now()->month)->count(),
            'alfa_hari_ini'         => Attendance::where('school_id', $school->id)->where('status', 'alfa')
                                        ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))->count(),
            'terlambat_hari_ini'    => Attendance::where('school_id', $school->id)->where('status', 'terlambat')
                                        ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))->count(),
            'siswa_bermasalah'      => Violation::where('school_id', $school->id)->where('is_archived', false)
                                        ->selectRaw('student_id')->groupBy('student_id')
                                        ->havingRaw('SUM(points) >= 50')->get()->count(),
        ];

        $recentViolations = Violation::where('school_id', $school->id)
            ->with(['student','category'])->latest()->take(8)->get();

        return view('dashboard.kesiswaan', compact('stats', 'recentViolations'));
    }

    public function siswa()
    {
        $student = auth()->user();

        $todayAttendance = Attendance::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))->first();

        $monthStats = Attendance::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->whereMonth('session_date', now()->month)->whereYear('session_date', now()->year))
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')
            ->pluck('total', 'status')->toArray();

        $hadirCount = ($monthStats['hadir'] ?? 0) + ($monthStats['terlambat'] ?? 0);
        $totalCount = array_sum($monthStats);
        $rate       = $totalCount > 0 ? round(($hadirCount / $totalCount) * 100, 1) : 0;

        $violationPoints = Violation::where('student_id', $student->id)->where('is_archived', false)->sum('points');

        // Tagihan aktif - kolom user_id bukan student_id
        $activeBills = PaymentBill::where('user_id', $student->id)
            ->whereIn('status', ['unpaid','partial'])->count();

        // Prakerin aktif
        $prakerinActive = PrakerinPlacement::where('student_id', $student->id)->where('is_active', true)->with('location')->first();

        return view('dashboard.siswa', compact(
            'todayAttendance', 'monthStats', 'rate', 'violationPoints', 'activeBills', 'prakerinActive'
        ));
    }

    public function bendahara()
    {
        $school = auth()->user()->school;

        $stats = [
            'tagihan_bulan_ini'   => PaymentBill::where('school_id', $school->id)->whereMonth('created_at', now()->month)->count(),
            'lunas_bulan_ini'     => PaymentBill::where('school_id', $school->id)->where('status', 'paid')->whereMonth('updated_at', now()->month)->count(),
            'menunggu_konfirmasi' => PaymentTransaction::where('school_id', $school->id)->where('status', 'pending')->where('channel', 'transfer')->count(),
            'total_tunggakan'     => PaymentBill::where('school_id', $school->id)->whereIn('status', ['unpaid','partial'])->count(),
        ];

        // Pemasukan bulan ini
        $pemasukanBulanIni = PaymentTransaction::where('school_id', $school->id)
            ->where('status', 'verified')->whereMonth('created_at', now()->month)->sum('amount');

        $pendingTransfers = PaymentTransaction::where('school_id', $school->id)
            ->where('status', 'pending')->where('channel', 'transfer')
            ->with(['bill.student','bill.paymentType'])->latest()->take(10)->get();

        // Tunggakan per kelas
        $tunggakanPerKelas = PaymentBill::where('school_id', $school->id)
            ->whereIn('status', ['unpaid','partial'])
            ->with('student.classrooms')
            ->get()
            ->groupBy(fn($b) => $b->student?->classrooms->first()?->name ?? 'Tanpa Kelas')
            ->map->count()
            ->sortDesc()->take(5);

        return view('dashboard.bendahara', compact(
            'stats', 'pendingTransfers', 'pemasukanBulanIni', 'tunggakanPerKelas'
        ));
    }

    public function kepala()
    {
        $school = auth()->user()->school;

        $stats = [
            'total_siswa'       => User::where('school_id', $school->id)->where('role', 'siswa')->where('is_active', true)->count(),
            'total_guru'        => User::where('school_id', $school->id)->whereIn('role', ['guru','wali_kelas'])->where('is_active', true)->count(),
            'tagihan_bulan_ini' => PaymentBill::where('school_id', $school->id)->whereMonth('created_at', now()->month)->count(),
            'tunggakan'         => PaymentBill::where('school_id', $school->id)->whereIn('status', ['unpaid','partial'])->count(),
            'hadir_hari_ini'    => Attendance::where('school_id', $school->id)->whereIn('status', ['hadir','terlambat'])
                                    ->whereHas('session', fn($q) => $q->whereDate('session_date', today()))->count(),
            'pelanggaran_bulan' => Violation::where('school_id', $school->id)->whereMonth('created_at', now()->month)->count(),
        ];

        // Rekap kelas hari ini
        $classroomSummary = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with(['major', 'students',
                'attendanceSessions' => fn($q) => $q->whereDate('session_date', today())->with('attendances')
            ])->get();

        // Pemasukan bulan ini
        $pemasukanBulan = PaymentTransaction::where('school_id', $school->id)
            ->where('status', 'verified')->whereMonth('created_at', now()->month)->sum('amount');

        return view('dashboard.kepala', compact('stats', 'classroomSummary', 'pemasukanBulan'));
    }
}
