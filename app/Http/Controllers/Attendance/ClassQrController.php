<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Services\AttendanceService;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ClassQrController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    /**
     * Halaman absensi siswa via QR permanen kelas.
     * URL: /absensi/kelas/{slug}
     * Token diambil dari cache — SAMA dengan yang ditampilkan di halaman guru.
     */
    public function scan(string $slug)
    {
        $classroom = Classroom::where('slug', $slug)
            ->with(['major', 'academicYear'])
            ->firstOrFail();

        $school = auth()->user()?->school
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

        // Cari sesi aktif hari ini
        $session = AttendanceSession::where('classroom_id', $classroom->id)
            ->whereDate('session_date', today())
            ->first();

        $isWithinTime = false;
        $plainToken   = null;

        if ($session && ! $session->is_closed) {
            $now          = now()->format('H:i:s');
            $isWithinTime = $now >= $session->open_time && $now <= $session->close_time;

            if ($isWithinTime) {
                // Ambil token dari cache — SAMA dengan token di halaman guru
                $plainToken = cache()->get("session_token_{$session->id}");

                // Jika cache kosong (misal server restart), generate token baru
                // dan update hash di DB agar konsisten
                if (! $plainToken) {
                    $plainToken = Str::random(40);
                    $tokenHash  = hash('sha256', $plainToken);

                    $session->update([
                        'qr_token_hash'   => $tokenHash,
                        'qr_generated_at' => now(),
                    ]);

                    cache()->put("session_token_{$session->id}", $plainToken, now()->addHours(10));
                }
            }
        }

        return view('attendance.student.class-scan', compact(
            'classroom', 'session', 'school', 'isWithinTime', 'plainToken'
        ));
    }

    /**
     * Halaman cetak QR permanen per kelas.
     * URL: /guru/absensi/kelas/{classroom}/cetak-qr
     * QR berisi URL permanen /absensi/kelas/{slug} — bukan token.
     * Token di-resolve server saat siswa buka URL tersebut.
     */
    public function print(Classroom $classroom)
    {
        $teacher = auth()->user();

        if ($classroom->school_id !== $teacher->school_id) {
            abort(403);
        }

        $school = $teacher->school;
        $classroom->load(['major', 'academicYear']);

        // URL PERMANEN — berisi slug kelas, bukan token
        // QR ini bisa dicetak dan ditempel permanen di papan kelas
        // Token di-resolve otomatis saat siswa scan
        $permanentUrl = config('app.url') . '/absensi/kelas/' . $classroom->slug;

        $qrImage = QrCode::format('svg')
            ->size(220)
            ->errorCorrection('H')
            ->generate($permanentUrl);

        return view('attendance.qr-print', compact(
            'classroom', 'school', 'qrImage', 'permanentUrl'
        ));
    }
}