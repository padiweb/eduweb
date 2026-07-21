<?php

namespace App\Http\Controllers\Admin\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\PrakerinAttendance;
use App\Models\PrakerinJournal;
use App\Models\PrakerinLocation;
use App\Models\PrakerinPeriod;
use App\Models\PrakerinPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecapController extends Controller
{
    private function school() { return Auth::user()->school; }

    /** Rekap absensi semua siswa */
    public function absensi(Request $request)
    {
        $school   = $this->school();
        $periods  = PrakerinPeriod::where('school_id', $school->id)->orderByDesc('start_date')->get();
        $periodId = $request->get('period_id', $periods->firstWhere('is_active', true)?->id ?? $periods->first()?->id);
        $locId    = $request->get('location_id');

        $locations = PrakerinLocation::where('school_id', $school->id)
            ->when($periodId, fn($q) => $q->where('period_id', $periodId))
            ->orderBy('name')->get();

        $placements = PrakerinPlacement::with(['student', 'location'])
            ->where('school_id', $school->id)
            ->when($periodId, fn($q) => $q->where('period_id', $periodId))
            ->when($locId,    fn($q) => $q->where('location_id', $locId))
            ->where('is_active', true)
            ->orderBy('student_id')
            ->get();

        // Hitung statistik per placement
        $stats = [];
        $period = $periods->find($periodId);

        foreach ($placements as $p) {
            $start = $p->start_date ?? $period?->start_date;
            $end   = min($p->end_date ?? $period?->end_date, today());

            $totalDays = $start && $end ? $start->diffInDays($end) + 1 : 0;

            $checkins = PrakerinAttendance::where('placement_id', $p->id)
                ->where('type', 'check_in')->get();

            $stats[$p->id] = [
                'total_days' => $totalDays,
                'hadir'      => $checkins->whereIn('status', ['hadir', 'terlambat'])->count(),
                'terlambat'  => $checkins->where('status', 'terlambat')->count(),
                'izin'       => $checkins->where('status', 'izin')->count(),
                'sakit'      => $checkins->where('status', 'sakit')->count(),
                'libur'      => $checkins->where('status', 'libur')->count(),
                'alfa'       => max(0, $totalDays - $checkins->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit', 'libur'])->count()),
                'jurnal'     => PrakerinJournal::where('placement_id', $p->id)->where('status', 'submitted')->count(),
            ];
        }

        return view('admin.prakerin.recap.absensi', compact(
            'placements', 'periods', 'periodId', 'locations', 'locId', 'stats', 'period'
        ));
    }

    /** Rekap jurnal semua siswa */
    public function jurnal(Request $request)
    {
        $school   = $this->school();
        $periods  = PrakerinPeriod::where('school_id', $school->id)->orderByDesc('start_date')->get();
        $periodId = $request->get('period_id', $periods->firstWhere('is_active', true)?->id ?? $periods->first()?->id);
        $locId    = $request->get('location_id');

        $locations = PrakerinLocation::where('school_id', $school->id)
            ->when($periodId, fn($q) => $q->where('period_id', $periodId))
            ->orderBy('name')->get();

        $journals = PrakerinJournal::with(['student', 'placement.location', 'photos'])
            ->whereHas('placement', fn($q) => $q
                ->where('school_id', $school->id)
                ->when($periodId, fn($q2) => $q2->where('period_id', $periodId))
                ->when($locId,    fn($q2) => $q2->where('location_id', $locId))
            )
            ->where('status', 'submitted')
            ->orderByDesc('journal_date')
            ->paginate(20)->withQueryString();

        return view('admin.prakerin.recap.jurnal', compact(
            'journals', 'periods', 'periodId', 'locations', 'locId'
        ));
    }
}
