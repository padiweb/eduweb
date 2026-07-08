<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{
    public function index()
    {
        $school       = auth()->user()->school;
        $activeYear   = AcademicYear::where('school_id', $school->id)->where('is_active', true)->first();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('name')->get();

        $classrooms = Classroom::where('school_id', $school->id)
            ->with(['major', 'academicYear', 'homeroomTeacher', 'students'])
            ->orderBy('grade')
            ->orderBy('name')
            ->get()
            ->groupBy('academic_year_id');

        $majors  = Major::where('school_id', $school->id)->orderBy('name')->get();
        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.classrooms.index', compact(
            'classrooms', 'academicYears', 'activeYear', 'majors', 'teachers'
        ));
    }

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'academic_year_id'   => ['required', 'exists:academic_years,id'],
            'major_id'           => ['nullable', 'exists:majors,id'],
            'name'               => ['required', 'string', 'max:30'],
            'grade'              => ['required', 'integer', 'min:1', 'max:13'],
            'homeroom_teacher_id'=> ['nullable', 'exists:users,id'],
        ]);

        // Cek duplikat nama kelas dalam tahun ajaran yang sama
        $exists = Classroom::where('school_id', $school->id)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kelas ' . $validated['name'] . ' sudah ada di tahun ajaran ini.');
        }

        Classroom::create(array_merge($validated, ['school_id' => $school->id]));

        // Jika guru wali kelas, update role
        if ($request->filled('homeroom_teacher_id')) {
            User::where('id', $request->homeroom_teacher_id)
                ->where('role', 'guru')
                ->update(['role' => 'wali_kelas']);
        }

        return back()->with('success', 'Kelas ' . $validated['name'] . ' berhasil dibuat.');
    }

    public function edit(Classroom $classroom)
    {
        $school = auth()->user()->school;
        if ($classroom->school_id !== $school->id) abort(403);

        $classroom->load(['major', 'academicYear', 'homeroomTeacher', 'students.studentDetail']);

        $majors   = Major::where('school_id', $school->id)->orderBy('name')->get();
        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->where('is_active', true)
            ->orderBy('name')->get();

        // Siswa yang belum ada di kelas manapun di tahun ajaran aktif, atau sudah di kelas ini
        $assignedIds = $classroom->students->pluck('id');
        $availableStudents = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->where('is_active', true)
            ->where(function ($q) use ($classroom, $assignedIds) {
                $q->whereDoesntHave('classrooms', function ($q2) use ($classroom) {
                    $q2->whereHas('academicYear', fn($q3) => $q3->where('is_active', true));
                })->orWhereIn('id', $assignedIds);
            })
            ->with('studentDetail')
            ->orderBy('name')
            ->get();

        return view('admin.classrooms.edit', compact(
            'classroom', 'majors', 'teachers', 'availableStudents'
        ));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $school = auth()->user()->school;
        if ($classroom->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'major_id'           => ['nullable', 'exists:majors,id'],
            'name'               => ['required', 'string', 'max:30'],
            'grade'              => ['required', 'integer', 'min:1', 'max:13'],
            'homeroom_teacher_id'=> ['nullable', 'exists:users,id'],
        ]);

        $oldTeacherId = $classroom->homeroom_teacher_id;
        $classroom->update($validated);

        // Update role guru jika wali kelas berubah
        if ($request->filled('homeroom_teacher_id') && $request->homeroom_teacher_id != $oldTeacherId) {
            User::where('id', $request->homeroom_teacher_id)
                ->where('role', 'guru')
                ->update(['role' => 'wali_kelas']);

            // Kembalikan role lama jika tidak pegang kelas lain
            if ($oldTeacherId) {
                $stillWali = Classroom::where('homeroom_teacher_id', $oldTeacherId)->exists();
                if (! $stillWali) {
                    User::where('id', $oldTeacherId)->where('role', 'wali_kelas')
                        ->update(['role' => 'guru']);
                }
            }
        }

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        $school = auth()->user()->school;
        if ($classroom->school_id !== $school->id) abort(403);

        if ($classroom->students()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus kelas yang masih ada siswanya.');
        }

        $classroom->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    // ── Assign siswa ke kelas ────────────────────────────────────────────

    public function assignStudent(Request $request, Classroom $classroom)
    {
        $school = auth()->user()->school;
        if ($classroom->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        // Cek apakah siswa sudah ada di kelas aktif lain
        $alreadyInClass = User::find($validated['student_id'])
            ->classrooms()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->where('classrooms.id', '!=', $classroom->id)
            ->exists();

        if ($alreadyInClass) {
            return back()->with('error', 'Siswa sudah ada di kelas lain pada tahun ajaran ini.');
        }

        // Cek apakah sudah ada di kelas ini
        if ($classroom->students()->where('student_id', $validated['student_id'])->exists()) {
            return back()->with('error', 'Siswa sudah ada di kelas ini.');
        }

        $classroom->students()->attach($validated['student_id']);

        return back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function removeStudent(Classroom $classroom, User $student)
    {
        $school = auth()->user()->school;
        if ($classroom->school_id !== $school->id) abort(403);

        $classroom->students()->detach($student->id);

        return back()->with('success', $student->name . ' berhasil dikeluarkan dari kelas.');
    }

    // ── Import siswa via CSV ─────────────────────────────────────────────

    public function importStudents(Request $request, Classroom $classroom)
    {
        $school = auth()->user()->school;
        if ($classroom->school_id !== $school->id) abort(403);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file    = $request->file('csv_file');
        $lines   = array_filter(array_map('trim', file($file->getRealPath())));
        $added   = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($lines as $i => $line) {
            if ($i === 0 && stripos($line, 'nis') !== false) continue; // skip header

            $parts = str_getcsv($line);
            $nis   = trim($parts[0] ?? '');
            if (empty($nis)) continue;

            $student = User::where('school_id', $school->id)
                ->where('role', 'siswa')
                ->where(function ($q) use ($nis) {
                    $q->where('nis', $nis)->orWhere('nisn', $nis);
                })
                ->first();

            if (! $student) {
                $errors[] = "NIS/NISN $nis tidak ditemukan.";
                $skipped++;
                continue;
            }

            // Sudah ada di kelas ini
            if ($classroom->students()->where('student_id', $student->id)->exists()) {
                $skipped++;
                continue;
            }

            $classroom->students()->attach($student->id);
            $added++;
        }

        $msg = "$added siswa berhasil ditambahkan.";
        if ($skipped > 0) $msg .= " $skipped dilewati.";
        if (count($errors) > 0) $msg .= ' Tidak ditemukan: ' . implode(', ', array_slice($errors, 0, 5));

        return back()->with('success', $msg);
    }
}
