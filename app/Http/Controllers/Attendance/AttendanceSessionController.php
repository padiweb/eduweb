<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Subject;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceSessionController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function index()
    {
        $teacher = auth()->user();

        $activeSessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->whereDate('session_date', today())
            ->with(['classroom', 'subject', 'attendances'])
            ->orderByDesc('created_at')
            ->get();

        $classrooms = Classroom::where('school_id', $teacher->school_id)
            ->with('major')
            ->get();

        return view('attendance.session.index', compact('activeSessions', 'classrooms'));
    }

    public function create(Request $request)
    {
        $teacher    = auth()->user();
        $classrooms = Classroom::where('school_id', $teacher->school_id)
            ->with(['major', 'schedules.subject'])
            ->get();

        $subjects = Subject::where('school_id', $teacher->school_id)->get();

        return view('attendance.session.create', compact('classrooms', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'classroom_id'     => ['required', 'exists:classrooms,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:60'],
        ]);

        $teacher = auth()->user();
        $school  = $teacher->school;

        if (! $school->latitude || ! $school->longitude) {
            return back()->with('error', 'Koordinat GPS sekolah belum diatur. Hubungi admin.');
        }

        try {
            $result = $this->service->openSession(
                school:          $school,
                teacher:         $teacher,
                classroomId:     (int) $validated['classroom_id'],
                subjectId:       (int) $validated['subject_id'],
                scheduleId:      null,
                durationMinutes: (int) $validated['duration_minutes'],
            );

            session()->put('qr_token_' . $result['session']->id, $result['plain_token']);

            return redirect()->route('guru.attendance.show', $result['session']->id)
                ->with('success', 'Sesi absensi berhasil dibuka.');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(AttendanceSession $session)
    {
        $teacher = auth()->user();

        if ($session->teacher_id !== $teacher->id) {
            abort(403);
        }

        $plainToken = session()->pull('qr_token_' . $session->id);

        if (! $plainToken && $session->isActive()) {
            $plainToken = $this->service->refreshToken($session);
        }

        $qrUrl   = null;
        $qrImage = null;

        if ($plainToken) {
            $qrUrl   = url('/absensi/scan?token=' . $plainToken);
            $qrImage = base64_encode(
                QrCode::format('svg')->size(280)->errorCorrection('H')->generate($qrUrl)
            );
        }

        $session->load(['classroom.students', 'subject', 'attendances.student']);
        $recap = $this->buildRecap($session);

        return view('attendance.session.show', compact('session', 'qrImage', 'qrUrl', 'recap', 'plainToken'));
    }

    public function close(AttendanceSession $session)
    {
        if ($session->teacher_id !== auth()->id()) abort(403);
        $session->close();
        return redirect()->route('guru.attendance.index')->with('success', 'Sesi ditutup.');
    }

    public function refreshQr(AttendanceSession $session, Request $request)
    {
        if ($session->teacher_id !== auth()->id()) abort(403);

        $duration   = (int) $request->input('duration_minutes', 10);
        $plainToken = $this->service->refreshToken($session, $duration);
        $qrUrl      = url('/absensi/scan?token=' . $plainToken);
        $qrImage = base64_encode(QrCode::format('svg')->size(280)->errorCorrection('H')->generate($qrUrl));

        return response()->json([
            'success'    => true,
            'qr_image'   => $qrImage,
            'expires_at' => $session->fresh()->token_expires_at->toISOString(),
        ]);
    }

    public function recap(AttendanceSession $session)
    {
        $session->load(['classroom.students', 'attendances.student']);
        return response()->json(['success' => true, 'recap' => $this->buildRecap($session)]);
    }

    public function rollCall(AttendanceSession $session)
    {
        if ($session->teacher_id !== auth()->id()) abort(403);
        $this->service->completeRollCall($session, auth()->user());
        return redirect()->route('guru.attendance.show', $session->id)
            ->with('success', 'Roll call selesai. Siswa yang tidak hadir ditandai Alfa.');
    }

    private function buildRecap(AttendanceSession $session): array
    {
        $total     = $session->classroom->students->count();
        $attended  = $session->attendances;

        return [
            'total'      => $total,
            'hadir'      => $attended->where('status', 'hadir')->count(),
            'terlambat'  => $attended->where('status', 'terlambat')->count(),
            'izin'       => $attended->where('status', 'izin')->count(),
            'sakit'      => $attended->where('status', 'sakit')->count(),
            'alfa'       => $attended->where('status', 'alfa')->count(),
            'belum'      => $total - $attended->count(),
            'rate'       => $total > 0
                ? round(($attended->whereIn('status', ['hadir', 'terlambat'])->count() / $total) * 100, 1)
                : 0,
            'attendances' => $attended->sortBy('scanned_at')->values(),
            'missing'     => $session->missing_students,
        ];
    }
}