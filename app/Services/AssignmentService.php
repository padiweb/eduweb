<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentService
{
    // ── Submit tugas siswa ─────────────────────────────────────────────────

    public function submit(
        Assignment $assignment,
        User       $student,
        array      $data
    ): AssignmentSubmission {
        return DB::transaction(function () use ($assignment, $student, $data) {

            $isLate = $assignment->deadline && now()->isAfter($assignment->deadline);

            $submission = AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'student_id'    => $student->id,
                ],
                [
                    'content'      => $data['content'] ?? null,
                    'file_path'    => $data['file_path'] ?? null,
                    'link_url'     => $data['link_url'] ?? null,
                    'status'       => $isLate ? 'late' : 'submitted',
                    'submitted_at' => now(),
                ]
            );

            return $submission;
        });
    }

    // ── Beri nilai ─────────────────────────────────────────────────────────

    public function grade(
        AssignmentSubmission $submission,
        int                  $score,
        ?string              $feedback,
        User                 $teacher
    ): AssignmentSubmission {
        $submission->update([
            'score'      => $score,
            'feedback'   => $feedback,
            'status'     => 'graded',
            'graded_at'  => now(),
            'graded_by'  => $teacher->id,
        ]);

        return $submission;
    }

    // ── Tutup tugas (guru) — buat poin pelanggaran untuk yang belum kumpul ─

    public function closeAssignment(Assignment $assignment, User $teacher): int
    {
        return DB::transaction(function () use ($assignment, $teacher) {

            $assignment->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);

            // Ambil semua siswa di kelas
            $students = $assignment->classroom->students;

            // Siswa yang sudah submit
            $submittedIds = $assignment->submissions()
                ->pluck('student_id')
                ->toArray();

            // Siswa yang belum submit sama sekali
            $notSubmitted = $students->whereNotIn('id', $submittedIds);

            $violationCount = 0;

            foreach ($notSubmitted as $student) {
                // Buat submission kosong dengan status tidak kumpul
                $submission = AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id'    => $student->id,
                    'status'        => 'submitted', // akan ditandai via violation
                    'submitted_at'  => now(),
                    'violation_created' => false,
                ]);

                // Buat poin pelanggaran tidak kumpul tugas (2 poin)
                $this->createAssignmentViolation(
                    $assignment,
                    $student,
                    'tugas_tidak_kumpul'
                );

                $violationCount++;
            }

            return $violationCount;
        });
    }

    // ── Hitung nilai akhir per mapel ───────────────────────────────────────

    public function getAverageScore(int $studentId, int $subjectId, int $classroomId): ?float
    {
        $scores = AssignmentSubmission::whereHas('assignment', function ($q) use ($subjectId, $classroomId) {
            $q->where('subject_id', $subjectId)
              ->where('classroom_id', $classroomId)
              ->where('is_closed', true);
        })
        ->where('student_id', $studentId)
        ->whereNotNull('score')
        ->pluck('score');

        if ($scores->isEmpty()) return null;

        return round($scores->avg(), 1);
    }

    // ── Rekap nilai per kelas per mapel ───────────────────────────────────

    public function getClassScores(int $classroomId, int $subjectId): array
    {
        $assignments = Assignment::where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->where('is_closed', true)
            ->with('submissions')
            ->orderBy('created_at')
            ->get();

        $students = \App\Models\Classroom::find($classroomId)
            ->students()
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($students as $student) {
            $scores = [];
            $total  = 0;
            $count  = 0;

            foreach ($assignments as $assignment) {
                $submission = $assignment->submissions
                    ->firstWhere('student_id', $student->id);

                $score = $submission?->score;
                $scores[$assignment->id] = $score;

                if ($score !== null) {
                    $total += $score;
                    $count++;
                }
            }

            $result[] = [
                'student'     => $student,
                'scores'      => $scores,
                'average'     => $count > 0 ? round($total / $count, 1) : null,
            ];
        }

        return [
            'assignments' => $assignments,
            'students'    => $result,
        ];
    }

    // ── Private: buat poin pelanggaran tugas ──────────────────────────────

    private function createAssignmentViolation(
        Assignment $assignment,
        User       $student,
        string     $source
    ): void {
        try {
            $points = match ($source) {
                'tugas_tidak_kumpul' => 2,
                default              => 1,
            };

            $names = [
                'tugas_tidak_kumpul' => 'Tidak Mengumpulkan Tugas',
            ];

            $category = ViolationCategory::firstOrCreate(
                ['school_id' => $assignment->school_id, 'name' => $names[$source]],
                ['severity' => 'sedang', 'default_points' => $points]
            );

            Violation::create([
                'school_id'     => $assignment->school_id,
                'student_id'    => $student->id,
                'category_id'   => $category->id,
                'reported_by'   => $assignment->teacher_id,
                'attendance_id' => null,
                'incident_date' => today(),
                'description'   => 'Tidak mengumpulkan tugas: ' . $assignment->title .
                                   ' (' . $assignment->subject->name . ')',
                'points'        => $points,
                'source'        => $source,
            ]);

        } catch (\Throwable $e) {
            Log::warning('Gagal buat pelanggaran tugas: ' . $e->getMessage());
        }
    }
}
