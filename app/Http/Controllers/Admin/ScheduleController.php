<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $school        = auth()->user()->school;
        $academicYears = AcademicYear::where('school_id', $school->id)
            ->orderByDesc('name')->get();
        $activeYear    = $academicYears->firstWhere('is_active', true);

        $classroomId = $request->get('classroom_id');

        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with('major')
            ->orderBy('grade')->orderBy('name')
            ->get();

        $subjects = Subject::where('school_id', $school->id)
            ->with('group')
            ->orderBy('name')->get();

        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->where('is_active', true)
            ->orderBy('name')->get();

        // Jadwal per kelas yang dipilih
        $schedules         = collect();
        $selectedClassroom = null;

        if ($classroomId) {
            $selectedClassroom = Classroom::find($classroomId);
            $schedules = Schedule::where('school_id', $school->id)
                ->where('classroom_id', $classroomId)
                ->with(['subject', 'teacher'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        // Peruntukan mapel: mapel → kelas mana saja yang punya jadwal mapel itu
        $subjectMapping = Schedule::where('school_id', $school->id)
            ->whereHas('classroom.academicYear', fn($q) => $q->where('is_active', true))
            ->with(['subject.group', 'classroom.major'])
            ->get()
            ->groupBy('subject_id')
            ->map(fn($rows) => [
                'subject' => $rows->first()->subject,
                'classes' => $rows->pluck('classroom')->unique('id')->values(),
            ])
            ->values()
            ->sortBy(fn($item) => $item['subject']->name);

        return view('admin.schedules.index', compact(
            'classrooms', 'subjects', 'teachers', 'schedules',
            'selectedClassroom', 'classroomId', 'subjectMapping'
        ));
    }

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'subject_id'   => ['required', 'exists:subjects,id'],
            'teacher_id'   => ['required', 'exists:users,id'],
            'day_of_week'  => ['required', 'integer', 'min:1', 'max:6'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'room'         => ['nullable', 'string', 'max:50'],
        ]);

        // Cek konflik jadwal guru di hari & jam yang sama
        $conflict = Schedule::where('school_id', $school->id)
            ->where('teacher_id', $validated['teacher_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Guru ini sudah punya jadwal di hari dan jam yang sama.');
        }

        Schedule::create(['school_id' => $school->id, ...$validated]);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $school = auth()->user()->school;
        if ($schedule->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'subject_id'  => ['required', 'exists:subjects,id'],
            'teacher_id'  => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'integer', 'min:1', 'max:6'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i', 'after:start_time'],
            'room'        => ['nullable', 'string', 'max:50'],
        ]);

        // Cek konflik kecuali jadwal ini sendiri
        $conflict = Schedule::where('school_id', $school->id)
            ->where('teacher_id', $validated['teacher_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('id', '!=', $schedule->id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })->exists();

        if ($conflict) {
            return back()->with('error', 'Guru ini sudah punya jadwal di hari dan jam yang sama.');
        }

        $schedule->update($validated);

        return redirect()->route('admin.schedules.index', ['classroom_id' => $schedule->classroom_id])
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $school = auth()->user()->school;
        if ($schedule->school_id !== $school->id) abort(403);

        $schedule->delete();
        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    // Jadwal per guru — untuk referensi guru melihat mapel yang dia ajar
    public function byTeacher(Request $request)
    {
        $school    = auth()->user()->school;
        $teacherId = $request->get('teacher_id');

        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->where('is_active', true)
            ->orderBy('name')->get();

        $schedules = collect();
        if ($teacherId) {
            $schedules = Schedule::where('school_id', $school->id)
                ->where('teacher_id', $teacherId)
                ->with(['subject', 'classroom'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        return view('admin.schedules.by-teacher', compact('teachers', 'schedules', 'teacherId'));
    }
}
