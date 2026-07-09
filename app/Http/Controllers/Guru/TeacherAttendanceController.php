<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherRewardPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherAttendanceController extends Controller
{
    // ── Halaman absensi guru ──────────────────────────────────────────────

    public function index()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;
        $today   = today();

        // Sesi hari ini
        $sessions = TeacherAttendanceSession::where('school_id', $school->id)
            ->where('session_date', $today)
            ->where('is_active', true)
            ->with(['attendances' => fn($q) => $q->where('teacher_id', $teacher->id)])
            ->orderBy('session_type')
            ->get();

        // Riwayat 30 hari terakhir
        $history = TeacherAttendance::where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->with('session')
            ->orderByDesc('scanned_at')
            ->take(30)
            ->get();

        // Total poin bulan ini
        $pointsThisMonth = TeacherRewardPoint::where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->whereMonth('point_date', now()->month)
            ->whereYear('point_date', now()->year)
            ->sum('points');

        // Total poin keseluruhan
        $pointsTotal = TeacherRewardPoint::where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->sum('points');

        return view('guru.attendance.index', compact(
            'sessions', 'history', 'pointsThisMonth', 'pointsTotal', 'school'
        ));
    }

    // ── Scan QR dari halaman guru ─────────────────────────────────────────

    public function scan(Request $request)
    {
        $teacher = auth()->user();
        $school  = $teacher->school;

        $validated = $request->validate([
            'qr_token'  => ['required', 'string'],
            'latitude'  => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        // Cari sesi yang sesuai token
        $session = TeacherAttendanceSession::where('school_id', $school->id)
            ->where('qr_token', $validated['qr_token'])
            ->where('session_date', today())
            ->where('is_active', true)
            ->first();

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'QR tidak valid atau sesi belum dibuka.'], 422);
        }

        if (! $session->isOpen()) {
            return response()->json(['success' => false, 'message' => 'Sesi absensi sudah ditutup.'], 422);
        }

        // Cek sudah absen
        if ($session->attendances()->where('teacher_id', $teacher->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Kamu sudah absen di sesi ini.'], 422);
        }

        // Hitung jarak GPS
        $distanceMeters = null;
        $isWithinRadius = false;

        if ($validated['latitude'] && $validated['longitude'] && $school->latitude && $school->longitude) {
            $distanceMeters = $this->calculateDistance(
                $validated['latitude'], $validated['longitude'],
                $school->latitude, $school->longitude
            );
            $isWithinRadius = $distanceMeters <= ($school->attendance_radius_meters ?? 200);
        }

        // Tolak jika di luar radius
        if ($school->latitude && ! $isWithinRadius && $distanceMeters !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu berada di luar area sekolah (' . round($distanceMeters) . 'm dari sekolah).',
            ], 422);
        }

        // Tentukan status
        $status = $session->session_type === 'pulang'
            ? 'hadir'
            : ($session->isLate() ? 'terlambat' : 'hadir');

        $attendance = TeacherAttendance::create([
            'school_id'       => $school->id,
            'session_id'      => $session->id,
            'teacher_id'      => $teacher->id,
            'status'          => $status,
            'latitude'        => $validated['latitude'],
            'longitude'       => $validated['longitude'],
            'distance_meters' => $distanceMeters,
            'is_within_radius'=> $isWithinRadius,
            'is_manual_entry' => false,
            'scanned_at'      => now(),
        ]);

        // Beri reward poin jika hadir tepat waktu di sesi masuk
        if ($session->session_type === 'masuk' && $status === 'hadir') {
            TeacherRewardPoint::create([
                'school_id'      => $school->id,
                'teacher_id'     => $teacher->id,
                'type'           => 'absen_tepat_waktu',
                'points'         => 1,
                'description'    => 'Absen masuk tepat waktu ' . today()->translatedFormat('d M Y'),
                'point_date'     => today(),
                'reference_id'   => $attendance->id,
                'reference_type' => 'teacher_attendance',
            ]);
        }

        $label = $session->session_type === 'masuk'
            ? ($status === 'terlambat' ? 'Terlambat' : 'Hadir Tepat Waktu')
            : 'Absen Pulang Berhasil';

        return response()->json([
            'success' => true,
            'message' => $label,
            'status'  => $status,
            'time'    => now()->format('H:i'),
        ]);
    }

    // ── Submit izin / sakit / dinas ───────────────────────────────────────

    public function submitStatus(Request $request)
    {
        $teacher = auth()->user();
        $school  = $teacher->school;

        $validated = $request->validate([
            'session_id' => ['required', 'exists:teacher_attendance_sessions,id'],
            'status'     => ['required', 'in:izin,sakit,dinas'],
            'notes'      => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $session = TeacherAttendanceSession::findOrFail($validated['session_id']);
        if ($session->school_id !== $school->id) abort(403);

        // Cek sudah absen
        if ($session->attendances()->where('teacher_id', $teacher->id)->exists()) {
            return back()->with('error', 'Kamu sudah absen di sesi ini.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store("teacher-attendance/{$school->id}", 'public');
        }

        TeacherAttendance::create([
            'school_id'       => $school->id,
            'session_id'      => $session->id,
            'teacher_id'      => $teacher->id,
            'status'          => $validated['status'],
            'notes'           => $validated['notes'] ?? null,
            'attachment_path' => $attachmentPath,
            'is_manual_entry' => false,
            'scanned_at'      => now(),
        ]);

        return back()->with('success', 'Status absensi berhasil disimpan.');
    }

    // ── Riwayat poin reward ───────────────────────────────────────────────

    public function rewards()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;

        $points = TeacherRewardPoint::where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('point_date')
            ->orderByDesc('created_at')
            ->paginate(30);

        $summary = [
            'total'       => TeacherRewardPoint::where('school_id', $school->id)->where('teacher_id', $teacher->id)->sum('points'),
            'this_month'  => TeacherRewardPoint::where('school_id', $school->id)->where('teacher_id', $teacher->id)->whereMonth('point_date', now()->month)->whereYear('point_date', now()->year)->sum('points'),
            'this_year'   => TeacherRewardPoint::where('school_id', $school->id)->where('teacher_id', $teacher->id)->whereYear('point_date', now()->year)->sum('points'),
        ];

        return view('guru.attendance.rewards', compact('points', 'summary'));
    }

    // ── Halaman QR untuk scan (bisa dari HP guru) ─────────────────────────

    public function scanPage()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;
        $today   = today();

        $sessions = TeacherAttendanceSession::where('school_id', $school->id)
            ->where('session_date', $today)
            ->where('is_active', true)
            ->with(['attendances' => fn($q) => $q->where('teacher_id', $teacher->id)])
            ->orderBy('session_type')
            ->get();

        return view('guru.attendance.scan', compact('sessions', 'school'));
    }

    // ── Helper: hitung jarak GPS (Haversine) ─────────────────────────────

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371000; // radius bumi dalam meter
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlam = deg2rad($lng2 - $lng1);
        $a    = sin($dphi/2)**2 + cos($phi1) * cos($phi2) * sin($dlam/2)**2;
        return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
    }
}
