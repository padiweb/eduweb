<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class StudentScanController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function landing(Request $request)
    {
        $token = $request->query('token');

        if (! $token) {
            abort(404, 'QR Code tidak valid.');
        }

        if (! auth()->check()) {
            session()->put('scan_token', $token);
            return redirect()->route('login')
                ->with('info', 'Login terlebih dahulu untuk melakukan absensi.');
        }

        if (auth()->user()->role !== 'siswa') {
            abort(403, 'Halaman ini hanya untuk siswa.');
        }

        return view('attendance.student.scan-confirm', compact('token'));
    }

    public function submit(Request $request)
    {
        $key = 'attendance:' . auth()->id();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'token'        => ['required', 'string'],
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
            'gps_accuracy' => ['required', 'numeric', 'min:0'],
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

            return response()->json([
                'success'      => true,
                'status'       => $attendance->status,
                'status_label' => $attendance->status_label,
                'scanned_at'   => $attendance->scanned_at->format('H:i:s'),
                'message'      => 'Absensi berhasil! Status: ' . $attendance->status_label,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function history()
    {
        $student = auth()->user();

        $attendances = \App\Models\Attendance::where('student_id', $student->id)
            ->with(['session.classroom', 'session.subject'])
            ->orderByDesc('scanned_at')
            ->paginate(20);

        $summary = \App\Models\Attendance::where('student_id', $student->id)
            ->whereMonth('scanned_at', now()->month)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('attendance.student.history', compact('attendances', 'summary'));
    }
}