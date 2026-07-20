<?php

namespace App\Http\Controllers\Admin\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\PrakerinAttendance;
use App\Models\PrakerinJournal;
use App\Models\PrakerinLocation;
use App\Models\PrakerinPeriod;
use App\Models\PrakerinPlacement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlacementController extends Controller
{
    private function school() { return Auth::user()->school; }

    public function index(Request $request)
    {
        $school   = $this->school();
        $periods  = PrakerinPeriod::where('school_id', $school->id)->orderByDesc('start_date')->get();
        $periodId = $request->get('period_id', $periods->firstWhere('is_active', true)?->id ?? $periods->first()?->id);

        $placements = PrakerinPlacement::with(['student', 'location', 'period'])
            ->where('school_id', $school->id)
            ->when($periodId, fn($q) => $q->where('period_id', $periodId))
            ->orderBy('created_at', 'desc')
            ->paginate(30)->withQueryString();

        // Untuk form tambah
        $locations = PrakerinLocation::where('school_id', $school->id)
            ->when($periodId, fn($q) => $q->where('period_id', $periodId))
            ->where('is_active', true)->orderBy('name')->get();

        $students = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->where('student_status', 'aktif')
            ->orderBy('name')->get();

        $activePeriod = $periods->find($periodId);

        return view('admin.prakerin.placements.index', compact(
            'placements', 'periods', 'periodId', 'locations', 'students', 'activePeriod'
        ));
    }

    public function store(Request $request)
    {
        $school = $this->school();
        $data   = $request->validate([
            'period_id'   => 'required|exists:prakerin_periods,id',
            'location_id' => 'required|exists:prakerin_locations,id',
            'student_id'  => 'required|exists:users,id',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'notes'       => 'nullable|string',
        ]);
        $data['school_id'] = $school->id;
        $data['is_active']  = true;

        PrakerinPlacement::create($data);
        return back()->with('success', 'Siswa berhasil ditempatkan.');
    }

    public function update(Request $request, PrakerinPlacement $placement)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:prakerin_locations,id',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'notes'       => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $placement->update($data);
        return back()->with('success', 'Penempatan diperbarui.');
    }

    public function destroy(PrakerinPlacement $placement)
    {
        $placement->delete();
        return back()->with('success', 'Penempatan dihapus.');
    }

    /** Rekap detail absensi + jurnal per siswa */
    public function show(PrakerinPlacement $placement)
    {
        $placement->load(['student', 'location.supervisors', 'period']);

        $start = $placement->getEffectiveStartDate();
        $end   = $placement->getEffectiveEndDate();

        $attendances = PrakerinAttendance::where('placement_id', $placement->id)
            ->orderBy('attendance_date')->orderBy('type')
            ->get()->groupBy(fn($a) => $a->attendance_date->format('Y-m-d'));

        $journals = PrakerinJournal::with('photos')
            ->where('placement_id', $placement->id)
            ->orderBy('journal_date')
            ->get()->keyBy(fn($j) => $j->journal_date->format('Y-m-d'));

        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $key = $current->format('Y-m-d');
            $dayAttendances = $attendances[$key] ?? collect();
            $days[$key] = [
                'date'    => $current->copy(),
                'checkin' => $dayAttendances->firstWhere('type', 'check_in'),
                'checkout'=> $dayAttendances->firstWhere('type', 'check_out'),
                'journal' => $journals[$key] ?? null,
            ];
            $current->addDay();
        }

        return view('admin.prakerin.placements.show', compact('placement', 'days'));
    }

    /** Tambah catatan guru di jurnal */
    public function addNote(PrakerinJournal $journal, Request $request)
    {
        $request->validate(['teacher_note' => 'required|string|max:1000']);
        $journal->update([
            'teacher_note' => $request->teacher_note,
            'noted_by'     => Auth::id(),
            'noted_at'     => now(),
        ]);
        return back()->with('success', 'Catatan disimpan.');
    }
}
