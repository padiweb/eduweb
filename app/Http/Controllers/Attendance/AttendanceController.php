<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    // ── Halaman utama — daftar kelas + status sesi hari ini ────────────────

    public function index()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;

        $classrooms = Classroom::where('school_id', $school->id)
            ->with(['major', 'students', 'academicYear'])
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $todaySessions = AttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->with(['classroom', 'openedBy', 'attendances'])
            ->get()
            ->keyBy('classroom_id');

        return view('attendance.index', compact('classrooms', 'todaySessions'));
    }

    // ── Buka / perbarui sesi untuk satu kelas ──────────────────────────────

    public function openSession(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
        ]);

        $teacher   = auth()->user();
        $school    = $teacher->school;
        $classroom = Classroom::findOrFail($validated['classroom_id']);

        if ($classroom->school_id !== $school->id) {
            abort(403);
        }

        if (! $school->latitude || ! $school->longitude) {
            return back()->with('error',
                'Koordinat GPS sekolah belum diatur. Hubungi admin untuk mengatur lokasi sekolah.'
            );
        }

        try {
            $result = $this->service->openOrRefreshSession($school, $classroom, $teacher);

            session()->put('qr_plain_token_' . $result['session']->id, $result['plain_token']);

            return redirect()
                ->route('guru.attendance.show', $result['session']->id)
                ->with('success', 'Sesi absensi ' . $classroom->name . ' berhasil dibuka.');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Halaman tampilkan QR + rekap real-time ─────────────────────────────

    public function show(AttendanceSession $session)
    {
        $teacher = auth()->user();

        if ($session->school_id !== $teacher->school_id) {
            abort(403);
        }

        $plainToken = session()->pull('qr_plain_token_' . $session->id);

        // Jika halaman di-refresh, buat token baru otomatis
        if (! $plainToken && $session->isActive()) {
            $result     = $this->service->openOrRefreshSession(
                $teacher->school,
                $session->classroom,
                $teacher
            );
            $plainToken = $result['plain_token'];
            $session    = $result['session'];
        }

        $qrUrl   = null;
        $qrImage = null;

        if ($plainToken) {
            $qrUrl   = url('/absensi/scan?token=' . $plainToken);
            $qrImage = base64_encode(
                QrCode::format('svg')->size(300)->errorCorrection('H')->generate($qrUrl)
            );
        }

        $session->load(['classroom.students', 'attendances.student', 'openedBy']);
        $recap = $this->buildRecap($session);

        return view('attendance.show', compact('session', 'qrImage', 'qrUrl', 'recap', 'plainToken'));
    }

    // ── Perbarui QR (AJAX) ─────────────────────────────────────────────────

    public function refreshQr(AttendanceSession $session)
    {
        $teacher = auth()->user();

        if ($session->school_id !== $teacher->school_id) {
            abort(403);
        }

        $result  = $this->service->openOrRefreshSession(
            $teacher->school,
            $session->classroom,
            $teacher
        );

        $qrUrl   = url('/absensi/scan?token=' . $result['plain_token']);
        $qrImage = base64_encode(
            QrCode::format('svg')->size(300)->errorCorrection('H')->generate($qrUrl)
        );

        return response()->json([
            'success'  => true,
            'qr_image' => $qrImage,
        ]);
    }

    // ── Rekap real-time (AJAX polling setiap 5 detik) ─────────────────────

    public function recap(AttendanceSession $session)
    {
        if ($session->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $session->load(['classroom.students', 'attendances.student']);

        return response()->json([
            'success' => true,
            'recap'   => $this->buildRecap($session),
        ]);
    }

    // ── Input manual guru (AJAX) ───────────────────────────────────────────

    public function manualEntry(AttendanceSession $session, Request $request)
    {
        $teacher = auth()->user();

        if ($session->school_id !== $teacher->school_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'student_id'        => ['required', 'exists:users,id'],
            'status'            => ['required', 'in:hadir,terlambat,izin,sakit,alfa'],
            'reason'            => ['required', 'string', 'min:5'],
            'permission_reason' => ['nullable', 'string'],
        ]);

        try {
            $attendance = $this->service->manualEntry(
                session:          $session,
                studentId:        (int) $validated['student_id'],
                status:           $validated['status'],
                reason:           $validated['reason'],
                teacher:          $teacher,
                permissionReason: $validated['permission_reason'] ?? null,
            );

            return response()->json([
                'success'      => true,
                'message'      => 'Absensi berhasil dicatat.',
                'status'       => $attendance->status,
                'status_label' => $attendance->status_label,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Roll call / validasi kehadiran fisik ───────────────────────────────

    public function rollCall(AttendanceSession $session, Request $request)
    {
        $teacher = auth()->user();

        if ($session->school_id !== $teacher->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'present_ids'   => ['nullable', 'array'],
            'present_ids.*' => ['exists:users,id'],
            'absent_ids'    => ['nullable', 'array'],
            'absent_ids.*'  => ['exists:users,id'],
            'subject_name'  => ['nullable', 'string', 'max:150'],
            'notes'         => ['nullable', 'string'],
        ]);

        try {
            $this->service->conductRollCall(
                session:           $session,
                presentStudentIds: $validated['present_ids'] ?? [],
                absentStudentIds:  $validated['absent_ids'] ?? [],
                teacher:           $teacher,
                subjectName:       $validated['subject_name'] ?? null,
                notes:             $validated['notes'] ?? null,
            );

            return redirect()
                ->route('guru.attendance.show', $session->id)
                ->with('success', 'Roll call selesai. Data absensi telah diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Tutup sesi — siswa yang belum absen → alfa otomatis ───────────────

    public function close(AttendanceSession $session)
    {
        $teacher = auth()->user();

        if ($session->school_id !== $teacher->school_id) {
            abort(403);
        }

        $this->service->autoAlfaOnClose($session);
        $session->close();

        return redirect()
            ->route('guru.attendance.index')
            ->with('success', 'Sesi absensi ' . $session->classroom->name . ' ditutup.');
    }

    // ── Private helper ─────────────────────────────────────────────────────

    private function buildRecap(AttendanceSession $session): array
    {
        $total    = $session->classroom->students->count();
        $attended = $session->attendances;

        return [
            'total'       => $total,
            'hadir'       => $attended->where('status', 'hadir')->count(),
            'terlambat'   => $attended->where('status', 'terlambat')->count(),
            'izin'        => $attended->where('status', 'izin')->count(),
            'sakit'       => $attended->where('status', 'sakit')->count(),
            'alfa'        => $attended->where('status', 'alfa')->count(),
            'belum'       => $total - $attended->count(),
            'rate'        => $total > 0
                ? round(
                    ($attended->whereIn('status', ['hadir', 'terlambat'])->count() / $total) * 100,
                    1
                  )
                : 0,
            'attendances' => $attended->sortBy('scanned_at')->values(),
            'missing'     => $session->missing_students,
        ];
    }
}