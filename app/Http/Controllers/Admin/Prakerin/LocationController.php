<?php

namespace App\Http\Controllers\Admin\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\PrakerinLocation;
use App\Models\PrakerinPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    private function school() { return Auth::user()->school; }

    /** Bersihkan field jam — ubah string kosong jadi null, potong HH:MM:SS jadi HH:MM */
    private function cleanTimeFields(Request $request): void
    {
        foreach (['checkin_time', 'checkout_time', 'checkin_late_after'] as $field) {
            $val = $request->input($field);
            if ($val === '' || $val === null) {
                $request->merge([$field => null]);
            } else {
                // Ambil hanya HH:MM (5 karakter pertama), buang detik jika ada
                $request->merge([$field => substr($val, 0, 5)]);
            }
        }
    }

    public function index(Request $request)
    {
        $school    = $this->school();
        $periods   = PrakerinPeriod::where('school_id', $school->id)->orderByDesc('start_date')->get();
        $periodId  = $request->get('period_id', $periods->firstWhere('is_active', true)?->id ?? $periods->first()?->id);

        $locations = PrakerinLocation::with(['supervisors', 'placements'])
            ->where('school_id', $school->id)
            ->when($periodId, fn($q) => $q->where('period_id', $periodId))
            ->orderBy('name')
            ->get();

        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas', 'admin'])
            ->orderBy('name')->get();

        $activePeriod = $periods->find($periodId);

        return view('admin.prakerin.locations.index', compact(
            'locations', 'periods', 'periodId', 'teachers', 'activePeriod'
        ));
    }

    public function store(Request $request)
    {
        $this->cleanTimeFields($request);
        $school = $this->school();

        $data = $request->validate([
            'period_id'              => 'required|exists:prakerin_periods,id',
            'name'                   => 'required|string|max:150',
            'address'                => 'nullable|string',
            'latitude'               => 'nullable|numeric|between:-90,90',
            'longitude'              => 'nullable|numeric|between:-180,180',
            'radius_meters'          => 'required|integer|min:50|max:2000',
            'field_supervisor_name'  => 'nullable|string|max:100',
            'field_supervisor_phone' => 'nullable|string|max:20',
            'checkin_time'           => 'nullable|string|max:5',
            'checkout_time'          => 'nullable|string|max:5',
            'checkin_late_after'     => 'nullable|string|max:5',
            'teacher_ids'            => 'nullable|array',
            'teacher_ids.*'          => 'exists:users,id',
        ]);

        $teacherIds = $data['teacher_ids'] ?? [];
        unset($data['teacher_ids']);
        $data['school_id'] = $school->id;
        $data['is_active']  = true;

        $location = PrakerinLocation::create($data);
        if ($teacherIds) {
            $location->supervisors()->sync($teacherIds);
        }

        return back()->with('success', 'DU/DI berhasil ditambahkan.');
    }

    public function update(Request $request, PrakerinLocation $location)
    {
        $this->cleanTimeFields($request);

        $data = $request->validate([
            'name'                   => 'required|string|max:150',
            'address'                => 'nullable|string',
            'latitude'               => 'nullable|numeric|between:-90,90',
            'longitude'              => 'nullable|numeric|between:-180,180',
            'radius_meters'          => 'required|integer|min:50|max:2000',
            'field_supervisor_name'  => 'nullable|string|max:100',
            'field_supervisor_phone' => 'nullable|string|max:20',
            'checkin_time'           => 'nullable|string|max:5',
            'checkout_time'          => 'nullable|string|max:5',
            'checkin_late_after'     => 'nullable|string|max:5',
            'teacher_ids'            => 'nullable|array',
            'teacher_ids.*'          => 'exists:users,id',
            'is_active'              => 'boolean',
        ]);

        $teacherIds = $data['teacher_ids'] ?? [];
        unset($data['teacher_ids']);
        $data['is_active'] = $request->boolean('is_active');

        $location->update($data);
        $location->supervisors()->sync($teacherIds);

        return back()->with('success', 'DU/DI diperbarui.');
    }

    public function destroy(PrakerinLocation $location)
    {
        if ($location->placements()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus DU/DI yang sudah memiliki siswa.');
        }
        $location->delete();
        return back()->with('success', 'DU/DI dihapus.');
    }
}
