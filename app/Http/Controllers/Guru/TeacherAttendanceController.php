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

        $now   = now()->format('H:i:s');
        $token = $validated['qr_token'];

        // Cari sesi yang sedang aktif — cek token di sesi atau di school
        $session = TeacherAttendanceSession::where('school_id', $school->id)
            ->where('session_date', today())
            ->where('is_active', true)
            ->where('open_time', '<=', $now)
            ->where('close_time', '>=', $now)
            ->where(function ($q) use ($token) {
                $q->where('qr_token', $token);
            })
            ->orderBy('session_type')
            ->first();

        // Fallback: token cocok dengan school dan ada sesi aktif
        if (! $session) {
            $schoolTokens = TeacherAttendanceSession::where('school_id', $school->id)
                ->where('session_date', today())
                ->pluck('qr_token')->toArray();

            $tokenValid = in_array($token, $schoolTokens) || $school->teacher_qr_token === $token;

            if ($tokenValid) {
                $session = TeacherAttendanceSession::where('school_id', $school->id)
                    ->where('session_date', today())
                    ->where('is_active', true)
                    ->where('open_time', '<=', $now)
                    ->where('close_time', '>=', $now)
                    ->orderBy('session_type')
                    ->first();
            }
        }

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'QR tidak valid atau tidak ada sesi yang aktif saat ini.'], 422);
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
            'attendance_date' => $session->session_date,
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
            'attendance_date' => $session->session_date,
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

    // ── Scan dari kamera HP (URL publik) ──────────────────────────────────

    public function scanFromUrl(string $token)
    {
        // Jika belum login, redirect ke login dulu
        if (! auth()->check()) {
            session()->put('url.intended', url()->current());
            return redirect()->route('login')
                ->with('info', 'Login terlebih dahulu untuk absensi.');
        }

        $teacher = auth()->user();
        $school  = $teacher->school;
        $now     = now()->format('H:i:s');

        // Cari sesi yang sedang aktif (dalam rentang jam) hari ini
        // Cek token di sesi ATAU di school (untuk kompatibilitas)
        $session = TeacherAttendanceSession::where('school_id', $school->id)
            ->where('session_date', today())
            ->where('is_active', true)
            ->where('open_time', '<=', $now)
            ->where('close_time', '>=', $now)
            ->where(function ($q) use ($token, $school) {
                $q->where('qr_token', $token)
                  ->orWhere(fn($q2) => $q2->whereRaw('1=1')->where(function() use ($q2, $token, $school) {
                      // fallback jika token cocok dengan school
                  }));
            })
            ->orderBy('session_type')
            ->first();

        // Jika tidak ketemu dengan filter jam+token, coba tanpa filter token
        // (cukup verifikasi bahwa token adalah milik sekolah ini)
        if (! $session) {
            $schoolTokens = TeacherAttendanceSession::where('school_id', $school->id)
                ->where('session_date', today())
                ->pluck('qr_token')
                ->toArray();

            $tokenValid = in_array($token, $schoolTokens) || $school->teacher_qr_token === $token;

            if ($tokenValid) {
                // Token valid, cari sesi yang sedang aktif jamnya
                $session = TeacherAttendanceSession::where('school_id', $school->id)
                    ->where('session_date', today())
                    ->where('is_active', true)
                    ->where('open_time', '<=', $now)
                    ->where('close_time', '>=', $now)
                    ->orderBy('session_type')
                    ->first();
            }
        }

        if (! $session) {
            // Token valid tapi jam belum/sudah lewat — beri pesan yang jelas
            $tokenValid = TeacherAttendanceSession::where('school_id', $school->id)
                ->where('session_date', today())
                ->where('qr_token', $token)
                ->exists() || $school->teacher_qr_token === $token;

            $sessionToday = TeacherAttendanceSession::where('school_id', $school->id)
                ->where('session_date', today())
                ->where('is_active', true)
                ->orderBy('open_time')
                ->get();

            $nextSession = $sessionToday->first(fn($s) => $s->open_time > $now);
            $message = $tokenValid
                ? 'QR valid. Tidak ada sesi absensi yang aktif saat ini.'
                : 'QR tidak valid untuk sekolah ini.';

            if ($tokenValid && $nextSession) {
                $message = 'Sesi ' . $nextSession->session_type . ' belum dibuka. Buka pukul ' . substr($nextSession->open_time, 0, 5) . ' WIB.';
            } elseif ($tokenValid && $sessionToday->isNotEmpty() && $now > $sessionToday->last()->close_time) {
                $message = 'Sesi absensi hari ini sudah berakhir.';
            }

            return view('guru.attendance.scan-result', [
                'success' => false,
                'message' => $message,
                'school'  => $school,
            ]);
        }

        // Cek sudah absen di sesi ini
        $existing = $session->attendances()
            ->where('teacher_id', $teacher->id)
            ->first();

        if ($existing) {
            return view('guru.attendance.scan-result', [
                'success'    => false,
                'message'    => 'Kamu sudah absen ' . ($session->session_type === 'masuk' ? 'masuk' : 'pulang') . ' hari ini.',
                'attendance' => $existing,
                'school'     => $school,
            ]);
        }

        // Tampilkan halaman konfirmasi + GPS
        return view('guru.attendance.scan-confirm', compact(
            'session', 'school', 'token'
        ));
    }

    // ── Proses absensi dari halaman scan-confirm ──────────────────────────

    public function confirmScan(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $teacher = auth()->user();
        $school  = $teacher->school;

        $validated = $request->validate([
            'token'     => ['required', 'string'],
            'latitude'  => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        // Validasi token — cari di sesi langsung, bukan di school
        $session = \App\Models\TeacherAttendanceSession::where('school_id', $school->id)
            ->where('qr_token', $validated['token'])
            ->where('session_date', today())
            ->where('is_active', true)
            ->orderBy('session_type')
            ->first();

        // Fallback: cari sesi aktif jika token cocok dengan school
        if (! $session && $school->teacher_qr_token === $validated['token']) {
            $session = \App\Models\TeacherAttendanceSession::where('school_id', $school->id)
                ->where('session_date', today())
                ->where('is_active', true)
                ->where(function ($q) {
                    $now = now()->format('H:i:s');
                    $q->where('open_time', '<=', $now)
                      ->where('close_time', '>=', $now);
                })
                ->orderBy('session_type')
                ->first();
        }

        if (! $session) {
            return back()->with('error', 'Sesi absensi tidak ditemukan atau sudah tutup.');
        }

        if ($session->attendances()->where('teacher_id', $teacher->id)->exists()) {
            return back()->with('error', 'Kamu sudah absen di sesi ini.');
        }

        // Hitung jarak GPS
        $distanceMeters = null;
        $isWithinRadius = true;

        if ($validated['latitude'] && $validated['longitude'] && $school->latitude && $school->longitude) {
            $distanceMeters = $this->calculateDistance(
                $validated['latitude'], $validated['longitude'],
                $school->latitude, $school->longitude
            );
            $isWithinRadius = $distanceMeters <= ($school->attendance_radius_meters ?? 200);

            if (! $isWithinRadius) {
                return back()->with('error', 'Kamu berada di luar area sekolah (' . round($distanceMeters) . 'm dari sekolah).');
            }
        }

        $status = $session->session_type === 'pulang'
            ? 'hadir'
            : ($session->isLate() ? 'terlambat' : 'hadir');

        $attendance = TeacherAttendance::create([
            'school_id'        => $school->id,
            'session_id'       => $session->id,
            'teacher_id'       => $teacher->id,
            'attendance_date'  => $session->session_date,
            'status'           => $status,
            'latitude'         => $validated['latitude'],
            'longitude'        => $validated['longitude'],
            'distance_meters'  => $distanceMeters,
            'is_within_radius' => $isWithinRadius,
            'is_manual_entry'  => false,
            'scanned_at'       => now(),
        ]);

        // Reward poin
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

        return view('guru.attendance.scan-result', [
            'success'    => true,
            'message'    => $status === 'hadir' ? 'Absensi berhasil!' : 'Terlambat — absensi tercatat.',
            'status'     => $status,
            'session'    => $session,
            'attendance' => $attendance,
            'school'     => $school,
        ]);
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