<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AcademicYear;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class StudentScanController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    // ── Landing page setelah scan QR ───────────────────────────────────────

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

        return view('attendance.student.scan', compact('token'));
    }

    // ── Submit absensi dari siswa (AJAX) ───────────────────────────────────

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

    // ── Riwayat absensi siswa ──────────────────────────────────────────────

    public function history(Request $request)
    {
        $student = auth()->user();

        // Default: bulan & tahun ini
        $month = (int) $request->get('bulan', now()->month);
        $year  = (int) $request->get('tahun', now()->year);

        // Rekap bulan ini
        $recap = $this->service->getMonthlyRecap($student, $month, $year);

        // Data per hari untuk ditampilkan
        $dailyRecords = $recap['records']->mapWithKeys(function ($att) {
            return [$att->session->session_date->format('Y-m-d') => $att];
        });

        // Daftar tahun ajaran untuk filter semester
        $academicYears = AcademicYear::where('school_id', $student->school_id)
            ->orderByDesc('name')
            ->get();

        // Rekap semester jika dipilih
        $semesterRecap = null;
        if ($request->has('semester_id') && $request->semester_id) {
            $semesterRecap = $this->service->getSemesterRecap(
                $student,
                (int) $request->semester_id
            );
        }

        // Generate daftar bulan untuk dropdown filter
        $months = collect(range(1, 12))->map(fn($m) => [
            'value' => $m,
            'label' => \Carbon\Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        // Generate daftar tahun (5 tahun ke belakang)
        $years = collect(range(now()->year, now()->year - 5))->map(fn($y) => [
            'value' => $y,
            'label' => (string) $y,
        ]);

        return view('attendance.student.history', compact(
            'recap',
            'dailyRecords',
            'academicYears',
            'semesterRecap',
            'months',
            'years',
            'month',
            'year'
        ));
    }
}