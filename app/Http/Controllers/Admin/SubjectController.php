<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\Subject;
use App\Models\SubjectGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    // ── Halaman utama: kelola kelompok + mapel ─────────────────────────────

    public function index()
    {
        $school = auth()->user()->school;

        $groups = SubjectGroup::where('school_id', $school->id)
            ->withCount('subjects')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('school_id', $school->id)
            ->with(['major', 'group'])
            ->withCount('schedules')
            ->orderBy('name')
            ->get()
            ->groupBy('subject_group_id');

        $majors = Major::where('school_id', $school->id)->orderBy('name')->get();

        return view('admin.subjects.index', compact('groups', 'subjects', 'majors'));
    }

    // ── CRUD Kelompok Mapel ────────────────────────────────────────────────

    public function storeGroup(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100',
                Rule::unique('subject_groups')->where('school_id', $school->id)
            ],
            'code'        => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        SubjectGroup::create(['school_id' => $school->id, ...$validated]);

        return back()->with('success', 'Kelompok mapel berhasil ditambahkan.');
    }

    public function updateGroup(Request $request, SubjectGroup $group)
    {
        $school = auth()->user()->school;
        if ($group->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100',
                Rule::unique('subject_groups')->where('school_id', $school->id)->ignore($group->id)
            ],
            'code'        => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $group->update($validated);
        return back()->with('success', 'Kelompok mapel berhasil diperbarui.');
    }

    public function destroyGroup(SubjectGroup $group)
    {
        $school = auth()->user()->school;
        if ($group->school_id !== $school->id) abort(403);

        if ($group->subjects()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus kelompok yang masih punya mata pelajaran.');
        }

        $group->delete();
        return back()->with('success', 'Kelompok mapel berhasil dihapus.');
    }

    // ── CRUD Mata Pelajaran ────────────────────────────────────────────────

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:150',
                Rule::unique('subjects')->where('school_id', $school->id)
            ],
            'code'             => ['nullable', 'string', 'max:20'],
            'subject_group_id' => ['nullable', 'exists:subject_groups,id'],
            'major_id'         => ['nullable', 'exists:majors,id'],
        ]);

        Subject::create(['school_id' => $school->id, ...$validated]);

        return back()->with('success', 'Mata pelajaran ' . $validated['name'] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, Subject $subject)
    {
        $school = auth()->user()->school;
        if ($subject->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:150',
                Rule::unique('subjects')->where('school_id', $school->id)->ignore($subject->id)
            ],
            'code'             => ['nullable', 'string', 'max:20'],
            'subject_group_id' => ['nullable', 'exists:subject_groups,id'],
            'major_id'         => ['nullable', 'exists:majors,id'],
        ]);

        $subject->update($validated);
        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        $school = auth()->user()->school;
        if ($subject->school_id !== $school->id) abort(403);

        if ($subject->schedules()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus mapel yang sudah ada jadwalnya.');
        }

        $subject->delete();
        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
