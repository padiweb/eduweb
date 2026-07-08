<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index()
    {
        $school       = auth()->user()->school;
        $academicYears = AcademicYear::where('school_id', $school->id)
            ->withCount('classrooms')
            ->orderByDesc('name')
            ->orderBy('semester')
            ->get();

        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:20',
                Rule::unique('academic_years')
                    ->where('school_id', $school->id)
                    ->where('semester', $request->semester)
            ],
            'semester'   => ['required', 'in:1,2'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
        ]);

        AcademicYear::create(['school_id' => $school->id, ...$validated]);

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function activate(AcademicYear $academicYear)
    {
        $school = auth()->user()->school;
        if ($academicYear->school_id !== $school->id) abort(403);

        DB::transaction(function () use ($academicYear, $school) {
            // Nonaktifkan semua tahun ajaran sekolah ini
            AcademicYear::where('school_id', $school->id)->update(['is_active' => false]);
            // Aktifkan yang dipilih
            $academicYear->update(['is_active' => true]);
        });

        return back()->with('success', $academicYear->label . ' sekarang aktif.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $school = auth()->user()->school;
        if ($academicYear->school_id !== $school->id) abort(403);

        if ($academicYear->is_active) {
            return back()->with('error', 'Tidak bisa hapus tahun ajaran yang sedang aktif.');
        }

        if ($academicYear->classrooms()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus tahun ajaran yang masih punya kelas.');
        }

        $academicYear->delete();
        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
