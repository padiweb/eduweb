<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Services\AttendanceService;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrManagementController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    /**
     * Halaman kelola QR semua kelas — khusus admin.
     */
    public function index()
    {
        $school = auth()->user()->school;

        $classrooms = Classroom::where('school_id', $school->id)
            ->with(['major', 'students', 'academicYear'])
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $todaySessions = AttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->get()
            ->keyBy('classroom_id');

        return view('admin.qr-management', compact('classrooms', 'todaySessions', 'school'));
    }

    /**
     * Perbarui token QR untuk satu kelas — hanya admin.
     * Jika sesi belum ada hari ini, otomatis dibuat dulu.
     */
    public function refreshToken(Classroom $classroom)
    {
        $school = auth()->user()->school;

        if ($classroom->school_id !== $school->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if (! $school->latitude || ! $school->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat GPS sekolah belum diatur. Atur dulu di Pengaturan Sekolah.',
            ], 422);
        }

        // Cari sesi hari ini — kalau belum ada, buat otomatis
        $session = AttendanceSession::where('classroom_id', $classroom->id)
            ->whereDate('session_date', today())
            ->first();

        $plainToken = Str::random(40);
        $tokenHash  = hash('sha256', $plainToken);

        if (! $session) {
            // Buat sesi baru
            $session = AttendanceSession::create([
                'school_id'        => $school->id,
                'classroom_id'     => $classroom->id,
                'opened_by'        => auth()->id(),
                'auto_created'     => false,
                'session_date'     => today(),
                'qr_token_hash'    => $tokenHash,
                'qr_generated_at'  => now(),
                'open_time'        => $school->school_start_time,
                'close_time'       => $school->attendance_close_time,
                'late_after'       => $school->late_threshold_time,
                'school_latitude'  => $school->latitude,
                'school_longitude' => $school->longitude,
                'radius_meters'    => $school->attendance_radius_meters,
            ]);
        } else {
            // Update token sesi yang sudah ada
            $session->update([
                'qr_token_hash'   => $tokenHash,
                'qr_generated_at' => now(),
                'is_closed'       => false,
                'closed_at'       => null,
            ]);
        }

        // Simpan plain token ke cache (dipakai halaman scan siswa)
        cache()->put("session_token_{$session->id}", $plainToken, now()->addHours(10));

        // Log perubahan
        ActivityLog::create([
            'school_id'  => $school->id,
            'user_id'    => auth()->id(),
            'user_name'  => auth()->user()->name,
            'user_role'  => 'admin',
            'action'     => 'qr.refresh',
            'new_values' => [
                'classroom'  => $classroom->name,
                'session_id' => $session->id,
                'created'    => $session->wasRecentlyCreated,
            ],
            'ip_address' => request()->ip(),
        ]);

        // Generate preview QR baru
        $qrUrl   = config('app.url') . '/absensi/scan?token=' . $plainToken;
        $qrImage = base64_encode(
            QrCode::format('svg')->size(200)->errorCorrection('H')->generate($qrUrl)
        );

        return response()->json([
            'success'         => true,
            'message'         => 'QR ' . $classroom->name . ' berhasil diperbarui.',
            'qr_image'        => $qrImage,
            'qr_generated_at' => now()->format('H:i:s'),
            'session_created' => $session->wasRecentlyCreated,
        ]);
    }
}