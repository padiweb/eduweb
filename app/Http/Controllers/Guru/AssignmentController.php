<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function __construct(private AssignmentService $service) {}

    public function index()
    {
        $school = auth()->user()->school;

        // Kelas aktif
        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with(['major', 'students'])
            ->orderBy('name')
            ->get();

        // Mapel guru ini dari jadwal (yang sudah diatur admin)
        $subjectIds = Schedule::where('teacher_id', auth()->id())
            ->where('school_id', $school->id)
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get();

        // Jika belum ada jadwal, ambil semua mapel sekolah sebagai fallback
        if ($subjects->isEmpty()) {
            $subjects = Subject::where('school_id', $school->id)
                ->orderBy('name')
                ->get();
        }

        $assignments = Assignment::where('teacher_id', auth()->id())
            ->with(['classroom', 'subject'])
            ->withCount('submissions')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('guru.assignments.index', compact('classrooms', 'subjects', 'assignments'));
    }

    public function show(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $assignment->load(['classroom.students', 'subject', 'submissions.student']);

        $students    = $assignment->classroom->students->sortBy('name');
        $submissions = $assignment->submissions->keyBy('student_id');

        return view('guru.assignments.show', compact('assignment', 'students', 'submissions'));
    }

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'classroom_id'    => ['required', 'exists:classrooms,id'],
            'subject_id'      => ['required', 'exists:subjects,id'],
            'title'           => ['required', 'string', 'max:200'],
            'description'     => ['nullable', 'string'],
            'attachment_path' => ['nullable', 'file', 'max:10240'], // file soal opsional
            'submission_type' => ['required', 'in:file,text,link,any'],
            'deadline'        => ['nullable', 'date', 'after:now'],
            'max_score'       => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        // Upload file soal jika ada
        $attachmentPath = null;
        if ($request->hasFile('attachment_path')) {
            $attachmentPath = $request->file('attachment_path')
                ->store("assignments/soal/{$school->id}", 'public');
        }

        Assignment::create([
            'school_id'       => $school->id,
            'classroom_id'    => $validated['classroom_id'],
            'subject_id'      => $validated['subject_id'],
            'teacher_id'      => auth()->id(),
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'attachment_path' => $attachmentPath,
            'submission_type' => $validated['submission_type'],
            'deadline'        => $validated['deadline'] ?? null,
            'max_score'       => $validated['max_score'],
        ]);

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function close(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        if ($assignment->is_closed) {
            return back()->with('error', 'Tugas sudah ditutup.');
        }

        $violationCount = $this->service->closeAssignment($assignment, auth()->user());

        return back()->with('success',
            'Tugas ditutup. ' .
            ($violationCount > 0
                ? $violationCount . ' siswa yang tidak mengumpulkan mendapat poin pelanggaran.'
                : 'Semua siswa sudah mengumpulkan.')
        );
    }

    public function grade(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'score'      => ['required', 'integer', 'min:0', 'max:' . $assignment->max_score],
            'feedback'   => ['nullable', 'string', 'max:1000'],
        ]);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();

        $this->service->grade(
            $submission,
            $validated['score'],
            $validated['feedback'] ?? null,
            auth()->user()
        );

        return response()->json([
            'success'  => true,
            'score'    => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
        ]);
    }

    // Guru lihat file tugas siswa (support multi-file by index)
    public function viewSubmissionFile(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        $this->authorizeAssignment($assignment);

        if (! $submission->file_path) abort(404, 'File tidak ditemukan.');

        $files = array_filter(explode(',', $submission->file_path));
        $index = (int) $request->get('index', 0);

        if (! isset($files[$index])) abort(404, 'File tidak ditemukan.');

        $path = trim($files[$index]);

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    public function scores(Request $request)
    {
        $school      = auth()->user()->school;
        $classroomId = $request->get('classroom_id');
        $subjectId   = $request->get('subject_id');

        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->orderBy('name')->get();

        $subjectIds = Schedule::where('teacher_id', auth()->id())
            ->where('school_id', $school->id)
            ->pluck('subject_id')->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        if ($subjects->isEmpty()) {
            $subjects = Subject::where('school_id', $school->id)->orderBy('name')->get();
        }

        $data = null;
        if ($classroomId && $subjectId) {
            $data = $this->service->getClassScores((int) $classroomId, (int) $subjectId);
        }

        return view('guru.assignments.scores', compact('classrooms', 'subjects', 'data', 'classroomId', 'subjectId'));
    }

    private function authorizeAssignment(Assignment $assignment): void
    {
        $school = auth()->user()->school;
        if ($assignment->school_id !== $school->id) abort(403);
    }
}