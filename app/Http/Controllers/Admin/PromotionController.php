<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    // ── Halaman promosi massal ─────────────────────────────────────────────

    public function index()
    {
        $school       = auth()->user()->school;
        $academicYears = AcademicYear::where('school_id', $school->id)
            ->orderByDesc('name')->orderBy('semester')->get();

        $activeYear = $academicYears->firstWhere('is_active', true);

        // Kelas dari tahun ajaran aktif untuk tujuan promosi
        $targetClassrooms = Classroom::where('school_id', $school->id)
            ->where('academic_year_id', $activeYear?->id)
            ->orderBy('grade')->orderBy('name')
            ->get();

        return view('admin.promotions.index', compact(
            'academicYears', 'activeYear', 'targetClassrooms'
        ));
    }

    // ── Load siswa dari tahun ajaran sumber ───────────────────────────────

    public function loadSource(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'source_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $classrooms = Classroom::where('school_id', $school->id)
            ->where('academic_year_id', $validated['source_year_id'])
            ->with(['students' => function ($q) {
                $q->where('student_status', 'aktif')->orderBy('name');
            }, 'major'])
            ->orderBy('grade')->orderBy('name')
            ->get();

        $activeYear = AcademicYear::where('school_id', $school->id)
            ->where('is_active', true)->first();

        $targetClassrooms = Classroom::where('school_id', $school->id)
            ->where('academic_year_id', $activeYear?->id)
            ->orderBy('grade')->orderBy('name')
            ->get();

        return view('admin.promotions.source', compact(
            'classrooms', 'targetClassrooms', 'activeYear', 'school'
        ));
    }

    // ── Proses promosi massal ─────────────────────────────────────────────

    public function process(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'promotions'                  => ['required', 'array'],
            'promotions.*.student_id'     => ['required', 'exists:users,id'],
            'promotions.*.action'         => ['required', 'in:naik,tidak_naik,lulus,keluar,pindah'],
            'promotions.*.target_class'   => ['nullable', 'exists:classrooms,id'],
            'promotions.*.notes'          => ['nullable', 'string', 'max:255'],
        ]);

        $results = ['naik' => 0, 'tidak_naik' => 0, 'lulus' => 0, 'keluar' => 0, 'pindah' => 0];

        DB::transaction(function () use ($validated, $school, &$results) {
            foreach ($validated['promotions'] as $item) {
                $student = User::where('id', $item['student_id'])
                    ->where('school_id', $school->id)
                    ->first();

                if (! $student) continue;

                $action = $item['action'];
                $notes  = $item['notes'] ?? null;

                match ($action) {
                    'naik', 'tidak_naik' => $this->promoteStudent(
                        $student, $item['target_class'] ?? null, $notes
                    ),
                    'lulus'  => $this->graduateStudent($student, $notes),
                    'keluar' => $this->changeStatus($student, 'keluar', $notes),
                    'pindah' => $this->changeStatus($student, 'pindah', $notes),
                };

                $results[$action]++;
            }
        });

        $msg = "Promosi selesai: {$results['naik']} naik kelas, {$results['tidak_naik']} tidak naik, {$results['lulus']} lulus";
        if ($results['keluar'] > 0) $msg .= ", {$results['keluar']} keluar";
        if ($results['pindah'] > 0) $msg .= ", {$results['pindah']} pindah";

        return redirect()->route('admin.classrooms.index')->with('success', $msg . '.');
    }

    // ── Pindah kelas individual (dalam tahun ajaran sama) ─────────────────

    public function transferStudent(Request $request, User $student)
    {
        $school = auth()->user()->school;
        if ($student->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'from_classroom_id' => ['required', 'exists:classrooms,id'],
            'to_classroom_id'   => ['required', 'exists:classrooms,id', 'different:from_classroom_id'],
            'notes'             => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($student, $validated) {
            // Hapus dari kelas asal
            $student->classrooms()->detach($validated['from_classroom_id']);
            // Tambah ke kelas tujuan
            $student->classrooms()->attach($validated['to_classroom_id']);
        });

        return back()->with('success', $student->name . ' berhasil dipindahkan ke kelas baru.');
    }

    // ── Update status siswa individual ─────────────────────────────────────

    public function updateStatus(Request $request, User $student)
    {
        $school = auth()->user()->school;
        if ($student->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'student_status' => ['required', 'in:aktif,alumni,keluar,pindah'],
            'status_notes'   => ['nullable', 'string', 'max:255'],
            'classroom_id'   => ['nullable', 'exists:classrooms,id'],
        ]);

        DB::transaction(function () use ($student, $validated) {
            $student->update([
                'student_status'   => $validated['student_status'],
                'status_notes'     => $validated['status_notes'] ?? null,
                'status_changed_at'=> today(),
                'is_active'        => $validated['student_status'] === 'aktif',
            ]);

            // Keluarkan dari kelas aktif jika keluar/pindah/alumni
            if (in_array($validated['student_status'], ['keluar', 'pindah', 'alumni'])) {
                if ($request->filled('classroom_id')) {
                    $student->classrooms()->detach($validated['classroom_id']);
                }
            }
        });

        return back()->with('success', 'Status ' . $student->name . ' berhasil diperbarui.');
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function promoteStudent(User $student, ?int $targetClassId, ?string $notes): void
    {
        if ($targetClassId) {
            $student->classrooms()->attach($targetClassId);
        }

        $student->update([
            'student_status'    => 'aktif',
            'status_notes'      => $notes,
            'status_changed_at' => today(),
        ]);
    }

    private function graduateStudent(User $student, ?string $notes): void
    {
        $student->update([
            'student_status'    => 'alumni',
            'status_notes'      => $notes ?? 'Lulus',
            'status_changed_at' => today(),
            'is_active'         => false,
        ]);
    }

    private function changeStatus(User $student, string $status, ?string $notes): void
    {
        $student->update([
            'student_status'    => $status,
            'status_notes'      => $notes,
            'status_changed_at' => today(),
            'is_active'         => false,
        ]);
    }
}
