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

    public function submit(Assignment $assignment, User $student, array $data): AssignmentSubmission
    {
        return DB::transaction(function () use ($assignment, $student, $data) {
            $isLate = $assignment->deadline && now()->isAfter($assignment->deadline);
            $status = $isLate ? 'late' : 'submitted';

            $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first();

            return AssignmentSubmission::updateOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $student->id],
                [
                    'content'      => $data['content'] ?? null,
                    'file_path'    => $data['file_path'] ?? ($existing?->file_path),
                    'link_url'     => $data['link_url'] ?? null,
                    'status'       => $status,
                    'submitted_at' => now(),
                ]
            );
        });
    }

    // ── Beri nilai ─────────────────────────────────────────────────────────

    public function grade(AssignmentSubmission $submission, int $score, ?string $feedback, User $teacher): AssignmentSubmission
    {
        $submission->update([
            'score'      => $score,
            'feedback'   => $feedback,
            'status'     => 'graded',
            'graded_at'  => now(),
            'graded_by'  => $teacher->id,
        ]);
        return $submission;
    }

    // ── Tutup tugas ────────────────────────────────────────────────────────

    public function closeAssignment(Assignment $assignment, User $teacher): int
    {
        return DB::transaction(function () use ($assignment, $teacher) {

            $assignment->update(['is_closed' => true, 'closed_at' => now()]);

            $students = $assignment->classroom->students;

            // ID siswa yang sudah punya submission
            $existingIds = $assignment->submissions()->pluck('student_id')->toArray();

            // Siswa yang belum submit sama sekali
            $notSubmitted = $students->whereNotIn('id', $existingIds);

            $count = 0;
            foreach ($notSubmitted as $student) {
                // Buat submission kosong dengan status not_submitted
                AssignmentSubmission::create([
                    'assignment_id'     => $assignment->id,
                    'student_id'        => $student->id,
                    'status'            => 'not_submitted',
                    'submitted_at'      => null, // tidak ada tanggal submit
                    'violation_created' => true,  // langsung true karena pelanggaran sudah dibuat
                ]);

                $this->createViolation($assignment, $student);
                $count++;
            }

            return $count;
        });
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
            ->students()->orderBy('name')->get();

        $result = [];
        foreach ($students as $student) {
            $scores = [];
            $total  = 0;
            $count  = 0;

            foreach ($assignments as $a) {
                $sub = $a->submissions->firstWhere('student_id', $student->id);
                // Simpan sebagai array agar view bisa bedakan score vs status
                $scores[$a->id] = [
                    'score'  => $sub?->score,
                    'status' => $sub?->status,
                ];
                if ($sub?->score !== null) {
                    $total += $sub->score;
                    $count++;
                }
            }

            $result[] = [
                'student' => $student,
                'scores'  => $scores,
                'average' => $count > 0 ? round($total / $count, 1) : null,
            ];
        }

        return ['assignments' => $assignments, 'students' => $result];
    }

    // ── Private: buat poin pelanggaran ────────────────────────────────────

    private function createViolation(Assignment $assignment, User $student): void
    {
        try {
            $category = ViolationCategory::firstOrCreate(
                ['school_id' => $assignment->school_id, 'name' => 'Tidak Mengumpulkan Tugas'],
                ['severity' => 'sedang', 'default_points' => 2]
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
                'points'        => 2,
                'source'        => 'tugas_tidak_kumpul',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal buat pelanggaran tugas: ' . $e->getMessage());
        }
    }
}