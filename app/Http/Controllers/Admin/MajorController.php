<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        $majors = Major::where('school_id', $school->id)
            ->withCount('classrooms')
            ->orderBy('name')
            ->get();

        return view('admin.majors.index', compact('majors'));
    }

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('majors')->where('school_id', $school->id)
            ],
            'code' => ['nullable', 'string', 'max:20'],
        ]);

        Major::create(['school_id' => $school->id, ...$validated]);

        return back()->with('success', 'Jurusan ' . $validated['name'] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, Major $major)
    {
        $school = auth()->user()->school;
        if ($major->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('majors')->where('school_id', $school->id)->ignore($major->id)
            ],
            'code' => ['nullable', 'string', 'max:20'],
        ]);

        $major->update($validated);
        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major)
    {
        $school = auth()->user()->school;
        if ($major->school_id !== $school->id) abort(403);

        if ($major->classrooms()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus jurusan yang masih punya kelas.');
        }

        $major->delete();
        return back()->with('success', 'Jurusan berhasil dihapus.');
    }
}
