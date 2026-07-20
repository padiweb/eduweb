<?php

namespace App\Http\Controllers\Admin\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PrakerinPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriodController extends Controller
{
    private function school() { return Auth::user()->school; }

    public function index()
    {
        $school  = $this->school();
        $periods = PrakerinPeriod::with(['academicYear', 'coordinators'])
            ->where('school_id', $school->id)
            ->orderByDesc('start_date')
            ->get();

        // Semua guru yang bisa jadi koordinator
        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas', 'admin', 'kesiswaan'])
            ->orderBy('name')->get();

        return view('admin.prakerin.periods.index', compact('periods', 'teachers'));
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
            'coordinator_ids'  => 'nullable|array',
            'coordinator_ids.*'=> 'exists:users,id',
        ]);

        $coordinatorIds = $data['coordinator_ids'] ?? [];
        unset($data['coordinator_ids']);
        $data['school_id'] = $school->id;
        $data['is_active']  = true;

        $period = PrakerinPeriod::create($data);
        if ($coordinatorIds) {
            $period->coordinators()->sync($coordinatorIds);
        }

        return back()->with('success', 'Periode prakerin berhasil dibuat.');
    }

    public function update(Request $request, PrakerinPeriod $period)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'description'      => 'nullable|string',
            'is_active'        => 'boolean',
            'coordinator_ids'  => 'nullable|array',
            'coordinator_ids.*'=> 'exists:users,id',
        ]);

        $coordinatorIds = $data['coordinator_ids'] ?? [];
        unset($data['coordinator_ids']);
        $data['is_active'] = $request->boolean('is_active');

        $period->update($data);
        $period->coordinators()->sync($coordinatorIds);

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

    /** Sync koordinator tanpa reload halaman (dari form koordinator di card periode) */
    public function syncCoordinators(Request $request, PrakerinPeriod $period)
    {
        $request->validate([
            'coordinator_ids'   => 'nullable|array',
            'coordinator_ids.*' => 'exists:users,id',
        ]);
        $period->coordinators()->sync($request->coordinator_ids ?? []);
        return back()->with('success', 'Koordinator prakerin diperbarui.');
    }
}
