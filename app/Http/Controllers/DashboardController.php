<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\PaymentBill;
use App\Models\PaymentTransaction;
use App\Models\PrakerinPlacement;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return match ($user->role) {
            'admin'             => redirect()->route('admin.dashboard'),
            'guru','wali_kelas' => redirect()->route('guru.dashboard'),
            'kesiswaan'         => redirect()->route('kesiswaan.dashboard'),
            'siswa'             => redirect()->route('siswa.siswa.dashboard'),
            'bendahara'         => redirect()->route('bendahara.dashboard'),
            'kepala_sekolah'    => redirect()->route('kepala.dashboard'),
            default             => abort(403, 'Role tidak dikenali.'),
        };
    }

    public function admin()
    {
        $school   = auth()->user()->school;
        $schoolId = $school->id;
        $today    = today();

        $userStats = User::where('school_id', $schoolId)
            ->where('is_active', true)
            ->selectRaw("SUM(role='siswa') as siswa, SUM(role IN ('guru','wali_kelas')) as guru")
            ->first();

        $kelasCount = Classroom::where('school_id', $schoolId)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->count();

        $absensiHariIni = Attendance::where('school_id', $schoolId)
            ->whereHas('session', fn($q) => $q->whereDate('session_date', $today))
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'siswa'          => (int) ($userStats->siswa ?? 0),
            'guru'           => (int) ($userStats->guru ?? 0),
            'kelas'          => $kelasCount,
            'hadir_hari_ini' => ($absensiHariIni['hadir'] ?? 0) + ($absensiHariIni['terlambat'] ?? 0),
            'alfa_hari_ini'  => $absensiHariIni['alfa'] ?? 0,
            'tunggakan'      => PaymentBill::where('school_id', $schoolId)
                ->whereIn('status', ['unpaid', 'partial'])->count(),
        ];

        $recentSessions = AttendanceSession::where('school_id', $schoolId)
            ->whereDate('session_date', $today)
            ->with(['classroom', 'openedBy', 'attendances'])
            ->latest()->take(10)->get();

        return view('dashboard.admin', compact('stats', 'recentSessions'));
    }

    public function guru()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;
        $today   = today();

        // Hanya kelas yang diampu guru ini
        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->whereHas('schedules', fn($q) => $q->where('teacher_id', $teacher->id))
            ->with(['major', 'students'])
            ->get();

        $classroomIds = $classrooms->pluck('id');

        // keyBy classroom_id agar view bisa akses: $todaySessions[$classroom->id]
        $todaySessions = AttendanceSession::where('school_id', $school->id)
            ->whereIn('classroom_id', $classroomIds)
            ->whereDate('session_date', $today)
            ->with(['classroom', 'attendances'])
            ->get()
            ->keyBy('classroom_id');

        $allAttendances = $todaySessions->flatMap->attendances;

        $stats = [
            'total_kelas' => $classrooms->count(),
            'total_hadir' => $allAttendances->whereIn('status', ['hadir', 'terlambat'])->count(),
            'total_alfa'  => $allAttendances->where('status', 'alfa')->count(),
        ];

        $jurnalBulanIni = TeachingJournal::where('teacher_id', $teacher->id)
            ->whereMonth('journal_date', now()->month)->count();

        $prakerinCount = PrakerinPlacement::whereHas('location.supervisors', fn($q) =>
            $q->where('prakerin_loc_supervisors.teacher_id', $teacher->id)
        )->where('is_active', true)->count();

        // KPI Guru: jurnal per bulan (6 bulan terakhir)
        $kpiJurnal = collect(range(5, 0))->map(function ($monthsAgo) use ($teacher) {
            $date = now()->subMonths($monthsAgo);
            return [
                'bulan' => $date->translatedFormat('M'),
                'total' => \App\Models\TeachingJournal::where('teacher_id', $teacher->id)
                    ->whereMonth('journal_date', $date->month)
                    ->whereYear('journal_date', $date->year)
                    ->count(),
            ];
        });

        // KPI: rata-rata nilai yang sudah dinilai
        $kpiNilai = \App\Models\AssignmentSubmission::whereHas('assignment', fn($q) =>
                $q->where('teacher_id', $teacher->id)
            )
            ->where('status', 'graded')
            ->whereNotNull('score')
            ->avg('score');

        // KPI: total tugas bulan ini
        $tugasBulanIni = \App\Models\Assignment::where('teacher_id', $teacher->id)
            ->whereMonth('created_at', now()->month)
            ->count();

        // KPI: absensi guru sendiri bulan ini
        $kpiAbsenGuru = \App\Models\TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('attendance_date', now()->month)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('dashboard.guru', compact(
            'stats', 'todaySessions', 'classrooms', 'jurnalBulanIni', 'prakerinCount',
            'kpiJurnal', 'kpiNilai', 'tugasBulanIni', 'kpiAbsenGuru'
        ));
    }

    public function kesiswaan()
    {
        $school   = auth()->user()->school;
        $schoolId = $school->id;
        $today    = today();

        $absensiHariIni = Attendance::where('school_id', $schoolId)
            ->whereHas('session', fn($q) => $q->whereDate('session_date', $today))
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $siswabermasalah = DB::table('violations')
            ->where('school_id', $schoolId)
            ->where('is_archived', false)
            ->groupBy('student_id')
            ->havingRaw('SUM(points) >= 50')
            ->count();

        $stats = [
            'pelanggaran_bulan_ini' => Violation::where('school_id', $schoolId)
                ->whereMonth('created_at', now()->month)->count(),
            'alfa_hari_ini'         => $absensiHariIni['alfa'] ?? 0,
            'terlambat_hari_ini'    => $absensiHariIni['terlambat'] ?? 0,
            'siswa_bermasalah'      => $siswabermasalah,
        ];

        $recentViolations = Violation::where('school_id', $schoolId)
            ->with(['student', 'category'])->latest()->take(8)->get();

        return view('dashboard.kesiswaan', compact('stats', 'recentViolations'));
    }

    public function siswa()
    {
        $student = auth()->user();
        $today   = today();

        $todayAttendance = Attendance::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->whereDate('session_date', $today))
            ->first();

        $monthStats = Attendance::where('student_id', $student->id)
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

        $violationPoints = Violation::where('student_id', $student->id)
            ->where('is_archived', false)->sum('points');

        $activeBills = PaymentBill::where('user_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])->count();

        $prakerinActive = PrakerinPlacement::where('student_id', $student->id)
            ->where('is_active', true)->with('location')->first();

        // Grafik kehadiran 7 hari terakhir
        $attendanceChart = collect(range(6, 0))->map(function ($daysAgo) use ($student) {
            $date   = today()->subDays($daysAgo);
            $status = Attendance::where('student_id', $student->id)
                ->whereHas('session', fn($q) => $q->whereDate('session_date', $date))
                ->value('status');
            return [
                'date'   => $date->format('d/m'),
                'day'    => $date->translatedFormat('D'),
                'status' => $status ?? 'none',
                'hadir'  => in_array($status, ['hadir','terlambat']) ? 1 : 0,
            ];
        });

        // Grafik nilai rata-rata per mapel
        $scoreChart = AssignmentSubmission::where('student_id', $student->id)
            ->where('status', 'graded')->whereNotNull('score')
            ->with('assignment.subject')
            ->orderBy('graded_at', 'desc')->take(30)->get()
            ->groupBy(fn($s) => $s->assignment->subject->name ?? 'Lainnya')
            ->map(fn($subs) => round($subs->avg('score'), 1))
            ->sortByDesc(fn($v) => $v)->take(6);

        // Nilai terbaru
        $recentScores = AssignmentSubmission::where('student_id', $student->id)
            ->where('status', 'graded')->whereNotNull('score')
            ->with('assignment.subject')->orderBy('graded_at', 'desc')->take(8)->get();

        return view('dashboard.siswa', compact(
            'todayAttendance', 'monthStats', 'rate',
            'violationPoints', 'activeBills', 'prakerinActive',
            'attendanceChart', 'scoreChart', 'recentScores'
        ));
    }

    public function bendahara()
    {
        $school   = auth()->user()->school;
        $schoolId = $school->id;

        $billStats = PaymentBill::where('school_id', $schoolId)
            ->selectRaw("
                SUM(MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())) as tagihan_bulan_ini,
                SUM(status IN ('unpaid','partial')) as total_tunggakan
            ")->first();

        $txStats = PaymentTransaction::where('school_id', $schoolId)
            ->selectRaw("
                SUM(status = 'pending' AND channel = 'transfer') as menunggu_konfirmasi
            ")->first();

        $pemasukanBulanIni = PaymentTransaction::where('school_id', $schoolId)
            ->where('status', 'verified')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $stats = [
            'tagihan_bulan_ini'   => (int) ($billStats->tagihan_bulan_ini ?? 0),
            'menunggu_konfirmasi' => (int) ($txStats->menunggu_konfirmasi ?? 0),
            'total_tunggakan'     => (int) ($billStats->total_tunggakan ?? 0),
        ];

        $pendingTransfers = PaymentTransaction::where('school_id', $schoolId)
            ->where('status', 'pending')->where('channel', 'transfer')
            ->with(['bill.student', 'bill.paymentType'])
            ->latest()->take(10)->get();

        // Tunggakan per kelas via JOIN (bukan N+1)
        // nama tabel pivot: classroom_students (dengan s)
        $tunggakanPerKelas = DB::table('payment_bills as pb')
            ->join('users as u', 'pb.user_id', '=', 'u.id')
            ->join('classroom_students as cs', 'u.id', '=', 'cs.student_id')
            ->join('classrooms as c', 'cs.classroom_id', '=', 'c.id')
            ->join('academic_years as ay', 'c.academic_year_id', '=', 'ay.id')
            ->where('pb.school_id', $schoolId)
            ->whereIn('pb.status', ['unpaid', 'partial'])
            ->where('ay.is_active', true)
            ->groupBy('c.name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
            ->select('c.name', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'name');

        return view('dashboard.bendahara', compact(
            'stats', 'pendingTransfers', 'pemasukanBulanIni', 'tunggakanPerKelas'
        ));
    }

    public function kepala()
    {
        $school   = auth()->user()->school;
        $schoolId = $school->id;
        $today    = today();

        $userStats = User::where('school_id', $schoolId)->where('is_active', true)
            ->selectRaw("SUM(role='siswa') as total_siswa, SUM(role IN ('guru','wali_kelas')) as total_guru")
            ->first();

        $absensiHariIni = Attendance::where('school_id', $schoolId)
            ->whereHas('session', fn($q) => $q->whereDate('session_date', $today))
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $billStats = PaymentBill::where('school_id', $schoolId)
            ->selectRaw("
                SUM(MONTH(created_at) = MONTH(NOW())) as tagihan_bulan_ini,
                SUM(status IN ('unpaid','partial')) as tunggakan
            ")->first();

        $stats = [
            'total_siswa'       => (int) ($userStats->total_siswa ?? 0),
            'total_guru'        => (int) ($userStats->total_guru ?? 0),
            'hadir_hari_ini'    => ($absensiHariIni['hadir'] ?? 0) + ($absensiHariIni['terlambat'] ?? 0),
            'tagihan_bulan_ini' => (int) ($billStats->tagihan_bulan_ini ?? 0),
            'tunggakan'         => (int) ($billStats->tunggakan ?? 0),
            'pelanggaran_bulan' => Violation::where('school_id', $schoolId)
                ->whereMonth('created_at', now()->month)->count(),
        ];

        $pemasukanBulan = PaymentTransaction::where('school_id', $schoolId)
            ->where('status', 'verified')
            ->whereMonth('created_at', now()->month)->sum('amount');

        $classroomSummary = Classroom::where('school_id', $schoolId)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with([
                'major',
                'students:id',
                'attendanceSessions' => fn($q) =>
                    $q->whereDate('session_date', $today)->with('attendances:id,session_id,status'),
            ])->get();

        return view('dashboard.kepala', compact('stats', 'classroomSummary', 'pemasukanBulan'));
    }
}
