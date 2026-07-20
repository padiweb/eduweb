<?php

namespace App\Http\Controllers\Admin\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PrakerinPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriodController extends Controller
{
    private function school() { return Auth::user()->school; }

    public function index()
    {
        $school  = $this->school();
        $periods = PrakerinPeriod::with('academicYear')
            ->where('school_id', $school->id)
            ->orderByDesc('start_date')
            ->get();
        return view('admin.prakerin.periods.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $school = $this->school();
        $data   = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => 'required|string|max:100',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'description'      => 'nullable|string',
        ]);
        $data['school_id'] = $school->id;
        $data['is_active']  = true;
        PrakerinPeriod::create($data);
        return back()->with('success', 'Periode prakerin berhasil dibuat.');
    }

    public function update(Request $request, PrakerinPeriod $period)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $period->update($data);
        return back()->with('success', 'Periode diperbarui.');
    }

    public function destroy(PrakerinPeriod $period)
    {
        if ($period->placements()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus periode yang sudah memiliki data penempatan siswa.');
        }
        $period->delete();
        return back()->with('success', 'Periode dihapus.');
    }
}
