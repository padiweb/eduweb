<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class StudentScanController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    // ── Landing page scan QR berbasis kelas (URL permanen) ──────────────────
    // URL: /absensi/kelas/{classroom}?token=xxx
    // Token dipakai validasi sekali scan, bukan untuk identifikasi kelas

    public function landingByClassroom(Request $request, Classroom $classroom)
    {
        $token = $request->query('token');

        if (! $token) {
            abort(404, 'QR Code tidak valid.');
        }

        if (! auth()->check()) {
            session()->put('scan_token_after_login', $token);
            session()->put('scan_classroom_after_login', $classroom->id);
            return redirect()->route('login')
                ->with('info', 'Silakan login terlebih dahulu untuk melakukan absensi.');
        }

        $student = auth()->user();

        if ($student->role !== 'siswa') {
            abort(403, 'Halaman ini hanya untuk siswa.');
        }

        // Cek apakah siswa sudah absen hari ini
        $todaySession = AttendanceSession::where('classroom_id', $classroom->id)
            ->whereDate('session_date', today())
            ->first();

        $alreadyAbsent = false;
        if ($todaySession) {
            $alreadyAbsent = Attendance::where('session_id', $todaySession->id)
                ->where('student_id', $student->id)
                ->exists();
        }

        return view('attendance.student.scan', compact('token', 'classroom', 'alreadyAbsent', 'todaySession'));
    }

    // ── Landing page scan QR lama (fallback) ────────────────────────────────

    public function landing(Request $request)
    {
        $token = $request->query('token');

        if (! $token) {
            abort(404, 'QR Code tidak valid.');
        }

        if (! auth()->check()) {
            session()->put('scan_token_after_login', $token);
            return redirect()->route('login')
                ->with('info', 'Silakan login terlebih dahulu untuk melakukan absensi.');
        }

        if (auth()->user()->role !== 'siswa') {
            abort(403, 'Halaman ini hanya untuk siswa.');
        }

        $classroom    = null;
        $alreadyAbsent = false;
        $todaySession  = null;

        return view('attendance.student.scan', compact('token', 'classroom', 'alreadyAbsent', 'todaySession'));
    }

    // ── Submit absensi siswa (AJAX) ──────────────────────────────────────────

    public function submit(Request $request)
    {
        $key = 'scan:' . auth()->id();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $wait = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$wait} detik.",
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'token'        => ['required', 'string'],
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
            'gps_accuracy' => ['required', 'numeric', 'min:0', 'max:500'],
        ]);

        $student = auth()->user();

        if ($student->role !== 'siswa') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        try {
            $attendance = $this->service->processStudentScan(
                plainToken:  $validated['token'],
                student:     $student,
                latitude:    (float) $validated['latitude'],
                longitude:   (float) $validated['longitude'],
                gpsAccuracy: (float) $validated['gps_accuracy'],
                ipAddress:   $request->ip(),
                userAgent:   $request->userAgent() ?? '',
            );

            RateLimiter::clear($key);

            $message = match ($attendance->status) {
                'hadir'     => 'Absensi berhasil! Kamu hadir tepat waktu.',
                'terlambat' => 'Absensi tercatat, namun kamu terlambat. Harap lebih tepat waktu!',
                default     => 'Absensi berhasil dicatat.',
            };

            return response()->json([
                'success'      => true,
                'status'       => $attendance->status,
                'status_label' => $attendance->status_label,
                'scanned_at'   => $attendance->scanned_at->format('H:i:s'),
                'is_late'      => $attendance->status === 'terlambat',
                'message'      => $message,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ── Riwayat absensi siswa ────────────────────────────────────────────────

    public function history(Request $request)
    {
        $student = auth()->user();

        $month = (int) $request->get('bulan', now()->month);
        $year  = (int) $request->get('tahun', now()->year);

        $recap = $this->service->getMonthlyRecap($student, $month, $year);

        $dailyRecords = $recap['records']->mapWithKeys(function ($att) {
            return [$att->session->session_date->format('Y-m-d') => $att];
        });

        $academicYears = AcademicYear::where('school_id', $student->school_id)
            ->orderByDesc('name')
            ->get();

        $semesterRecap = null;
        if ($request->has('semester_id') && $request->semester_id) {
            $semesterRecap = $this->service->getSemesterRecap(
                $student,
                (int) $request->semester_id
            );
        }

        $months = collect(range(1, 12))->map(fn($m) => [
            'value' => $m,
            'label' => \Carbon\Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        $years = collect(range(now()->year, now()->year - 5))->map(fn($y) => [
            'value' => $y,
            'label' => (string) $y,
        ]);

        return view('attendance.student.history', compact(
            'recap', 'dailyRecords', 'academicYears',
            'semesterRecap', 'months', 'years', 'month', 'year'
        ));
    }
}
