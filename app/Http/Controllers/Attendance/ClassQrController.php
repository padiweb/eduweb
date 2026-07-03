<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ClassQrController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    /**
     * Halaman yang dibuka siswa setelah scan QR permanen di kelas.
     * URL: /absensi/kelas/{slug}
     *
     * QR yang ditempel di papan kelas berisi URL ini (permanen, tidak berubah).
     * Server yang resolve token aktif hari ini secara otomatis.
     */
    public function scan(string $slug)
    {
        $classroom = Classroom::where('slug', $slug)
            ->with(['major', 'academicYear.school'])
            ->firstOrFail();

        $school = $classroom->academicYear->school
               ?? \App\Models\School::where('id', $classroom->school_id)->first();

        // Redirect ke login jika belum auth
        if (! auth()->check()) {
            session()->put('url.intended', url()->current());
            return redirect()->route('login')
                ->with('info', 'Login terlebih dahulu untuk melakukan absensi.');
        }

        if (auth()->user()->role !== 'siswa') {
            abort(403, 'Halaman ini hanya untuk siswa.');
        }

        // Cari sesi aktif hari ini untuk kelas ini
        $session = AttendanceSession::where('classroom_id', $classroom->id)
            ->whereDate('session_date', today())
            ->first();

        // Cek apakah dalam jam absensi
        $isWithinTime = false;
        $plainToken   = null;

        if ($session && ! $session->is_closed) {
            $now          = now()->format('H:i:s');
            $isWithinTime = $now >= $session->open_time && $now <= $session->close_time;

            if ($isWithinTime) {
                // Generate token fresh untuk siswa ini
                // Token di-refresh setiap akses halaman ini
                $plainToken = $this->service->refreshToken($session);
            }
        }

        return view('attendance.student.class-scan', compact(
            'classroom', 'session', 'school', 'isWithinTime', 'plainToken'
        ));
    }

    /**
     * Halaman cetak QR permanen untuk satu kelas.
     * URL: /guru/absensi/kelas/{classroom}/cetak-qr
     * Hanya bisa diakses guru/admin.
     */
    public function print(Classroom $classroom)
    {
        $teacher = auth()->user();

        if ($classroom->school_id !== $teacher->school_id) {
            abort(403);
        }

        $school    = $teacher->school;
        $classroom->load(['major', 'academicYear']);

        // URL permanen yang di-encode ke QR
        // URL ini tidak berubah — hanya berisi slug kelas
        $permanentUrl = config('app.url') . '/absensi/kelas/' . $classroom->slug;

        // Generate QR SVG
        $qrImage = QrCode::format('svg')
            ->size(220)
            ->errorCorrection('H')
            ->generate($permanentUrl);

        return view('attendance.qr-print', compact('classroom', 'school', 'qrImage', 'permanentUrl'));
    }
}
