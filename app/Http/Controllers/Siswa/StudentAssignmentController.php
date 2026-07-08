<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentAssignmentController extends Controller
{
    public function __construct(private AssignmentService $service) {}

    public function index()
    {
        $student   = auth()->user();
        $classroom = $student->classrooms()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->first();

        if (! $classroom) {
            return view('siswa.assignments.index', [
                'activeAssignments'  => collect(),
                'closedAssignments'  => collect(),
                'classroom'          => null,
            ]);
        }

        $assignments = Assignment::where('classroom_id', $classroom->id)
            ->with(['subject', 'teacher',
                'submissions' => fn($q) => $q->where('student_id', $student->id)
            ])
            ->orderByDesc('created_at')
            ->get();

        $activeAssignments = $assignments->where('is_closed', false)->values();
        $closedAssignments = $assignments->where('is_closed', true)->values();

        return view('siswa.assignments.index', compact(
            'activeAssignments', 'closedAssignments', 'classroom'
        ));
    }

    public function show(Assignment $assignment)
    {
        $student = auth()->user();

        $inClass = $student->classrooms()
            ->where('classrooms.id', $assignment->classroom_id)
            ->exists();

        if (! $inClass) abort(403);

        $assignment->load(['subject', 'teacher']);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        return view('siswa.assignments.show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $student = auth()->user();

        if ($assignment->is_closed) {
            return back()->with('error', 'Tugas sudah ditutup. Tidak bisa mengumpulkan.');
        }

        $rules = [];
        if (in_array($assignment->submission_type, ['text', 'any'])) {
            $rules['content']  = ['nullable', 'string', 'max:10000'];
        }
        if (in_array($assignment->submission_type, ['file', 'any'])) {
            $rules['file']     = ['nullable', 'file', 'max:51200']; // 50MB, semua jenis file
        }
        if (in_array($assignment->submission_type, ['link', 'any'])) {
            $rules['link_url'] = ['nullable', 'url', 'max:500'];
        }

        $validated = $request->validate($rules);

        if (empty($validated['content']) && ! $request->hasFile('file') && empty($validated['link_url'])) {
            return back()->with('error', 'Isi minimal satu: teks, file, atau link.');
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store(
                "assignments/{$assignment->school_id}/{$assignment->id}/{$student->id}",
                'public'
            );
        }

        $this->service->submit($assignment, $student, [
            'content'   => $validated['content'] ?? null,
            'file_path' => $filePath,
            'link_url'  => $validated['link_url'] ?? null,
        ]);

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    // Siswa lihat file tugasnya sendiri
    public function viewFile(Assignment $assignment)
    {
        $student = auth()->user();

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if (! $submission->file_path || ! Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file(Storage::disk('public')->path($submission->file_path));
    }

    public function scores()
    {
        $student   = auth()->user();
        $classroom = $student->classrooms()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->first();

        if (! $classroom) {
            return view('siswa.assignments.scores', [
                'subjectScores' => collect(),
                'classroom'     => null,
            ]);
        }

        // Ambil mapel yang ada tugasnya di kelas ini (TANPA relasi subjects di classroom)
        $subjects = Subject::whereHas('assignments', fn($q) =>
            $q->where('classroom_id', $classroom->id)->where('is_closed', true)
        )->orderBy('name')->get();

        $subjectScores = $subjects->map(function ($subject) use ($student, $classroom) {
            $assignments = Assignment::where('classroom_id', $classroom->id)
                ->where('subject_id', $subject->id)
                ->where('is_closed', true)
                ->with(['submissions' => fn($q) => $q->where('student_id', $student->id)])
                ->orderBy('created_at')
                ->get();

            $gradedCount = 0;
            $totalScore  = 0;

            foreach ($assignments as $a) {
                $sub = $a->submissions->first();
                if ($sub && $sub->score !== null) {
                    $totalScore += $sub->score;
                    $gradedCount++;
                }
            }

            return [
                'subject'      => $subject,
                'assignments'  => $assignments,
                'average'      => $gradedCount > 0 ? round($totalScore / $gradedCount, 1) : null,
                'total'        => $assignments->count(),
                'graded_count' => $gradedCount,
            ];
        });

        return view('siswa.assignments.scores', compact('subjectScores', 'classroom'));
    }
}