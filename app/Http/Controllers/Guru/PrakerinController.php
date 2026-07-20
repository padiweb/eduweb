<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PrakerinAttendance;
use App\Models\PrakerinJournal;
use App\Models\PrakerinLocation;
use App\Models\PrakerinPeriod;
use App\Models\PrakerinPlacement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller guru pembimbing prakerin.
 *
 * Siapa yang dapat akses:
 * - Guru yang jadi pembimbing (supervisor) di minimal 1 DU/DI
 *   dalam periode aktif → dapat menu Prakerin di sidebar.
 *
 * Batasan akses:
 * - Update koordinat/jam DU/DI: hanya lokasi yang dia bimbing
 * - Tambah penempatan siswa: hanya ke lokasi yang dia bimbing
 * - Rekap absensi & jurnal: default lokasi yang dia bimbing,
 *   tapi bisa filter ke semua lokasi dalam periode
 */
class PrakerinController extends Controller
{
    private function school()  { return Auth::user()->school; }
    private function teacher() { return Auth::user(); }

    /** Periode di mana guru ini jadi pembimbing DU/DI */
    private function myPeriods()
    {
        return PrakerinPeriod::with(['locations.supervisors'])
            ->where('school_id', $this->school()->id)
            ->where('is_active', true)
            ->whereHas('locations.supervisors', fn($q) => $q->where('teacher_id', $this->teacher()->id))
            ->orderByDesc('start_date')
            ->get();
    }

    /** Lokasi yang dibimbing guru ini dalam periode tertentu */
    private function myLocationIds(int $periodId)
    {
        return PrakerinLocation::where('period_id', $periodId)
            ->whereHas('supervisors', fn($q) => $q->where('teacher_id', $this->teacher()->id))
            ->pluck('id');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $periods  = $this->myPeriods();
        $periodId = $request->get('period_id', $periods->first()?->id);
        $period   = $periods->find($periodId);

        $myLocations = collect();
        if ($period) {
            $myLocIds    = $this->myLocationIds($period->id);
            $myLocations = PrakerinLocation::with(['placements.student', 'supervisors'])
                ->whereIn('id', $myLocIds)->get();
        }

        return view('guru.prakerin.index', compact('periods', 'period', 'periodId', 'myLocations'));
    }

    // ── Kelola DU/DI (update koordinat, jam, tambah siswa) ───────────────

    public function locationIndex(Request $request)
    {
        $periods  = $this->myPeriods();
        $periodId = $request->get('period_id', $periods->first()?->id);
        $period   = $periods->find($periodId);

        $locations = collect();
        $teachers  = collect();
        $locationsJs = collect();
        if ($period) {
            $myLocIds  = $this->myLocationIds($period->id);
            $locations = PrakerinLocation::with(['supervisors', 'placements'])
                ->whereIn('id', $myLocIds)->orderBy('name')->get();
            $teachers  = User::where('school_id', $this->school()->id)
                ->whereIn('role', ['guru', 'wali_kelas', 'admin', 'kesiswaan'])
                ->orderBy('name')->get();

            // Siapkan data untuk JS modal (hindari @json arrow function di Blade)
            $locationsJs = $locations->map(function($l) {
                return [
                    'id'                     => $l->id,
                    'name'                   => $l->name,
                    'address'                => $l->address ?? '',
                    'field_supervisor_name'  => $l->field_supervisor_name ?? '',
                    'field_supervisor_phone' => $l->field_supervisor_phone ?? '',
                    'latitude'               => $l->latitude,
                    'longitude'              => $l->longitude,
                    'radius_meters'          => $l->radius_meters ?? 300,
                    'checkin_time'           => $l->checkin_time ?? '',
                    'checkin_late_after'     => $l->checkin_late_after ?? '',
                    'checkout_time'          => $l->checkout_time ?? '',
                ];
            })->values();
        }

        return view('guru.prakerin.locations', compact('periods', 'period', 'periodId', 'locations', 'teachers', 'locationsJs'));
    }

    public function locationUpdate(Request $request, PrakerinLocation $location)
    {
        // Pastikan guru ini pembimbing di lokasi ini
        abort_unless(
            $location->supervisors()->where('teacher_id', $this->teacher()->id)->exists(),
            403, 'Anda bukan pembimbing di lokasi ini.'
        );

        $data = $request->validate([
            // Identitas DU/DI — boleh diubah pembimbing
            'name'                   => 'required|string|max:150',
            'address'                => 'nullable|string',
            'field_supervisor_name'  => 'nullable|string|max:100',
            'field_supervisor_phone' => 'nullable|string|max:20',
            // GPS & jam
    public function locationUpdate(Request $request, PrakerinLocation $location)
    {
        // Pastikan guru ini pembimbing di lokasi ini
        abort_unless(
            $location->supervisors()->where('teacher_id', $this->teacher()->id)->exists(),
            403, 'Anda bukan pembimbing di lokasi ini.'
        );

        // Bersihkan string kosong jadi null
        foreach (['checkin_time', 'checkout_time', 'checkin_late_after'] as $field) {
            if ($request->input($field) === '') $request->merge([$field => null]);
        }

        $data = $request->validate([
            'name'                   => 'required|string|max:150',
            'address'                => 'nullable|string',
            'field_supervisor_name'  => 'nullable|string|max:100',
            'field_supervisor_phone' => 'nullable|string|max:20',
            'latitude'               => 'nullable|numeric|between:-90,90',
            'longitude'              => 'nullable|numeric|between:-180,180',
            'radius_meters'          => 'required|integer|min:50|max:2000',
            'checkin_time'           => 'nullable|string|max:5',
            'checkout_time'          => 'nullable|string|max:5',
            'checkin_late_after'     => 'nullable|string|max:5',
        ]);

        $location->update($data);
        return back()->with('success', 'Data DU/DI berhasil diperbarui.');
    }

    // ── Kelola Penempatan Siswa ───────────────────────────────────────────

    public function placementIndex(Request $request)
    {
        $periods  = $this->myPeriods();
        $periodId = $request->get('period_id', $periods->first()?->id);
        $locId    = $request->get('location_id');
        $period   = $periods->find($periodId);

        $locations      = collect();
        $placements     = collect();
        $students       = collect();
        $placedStudents = collect();

        if ($period) {
            $myLocIds  = $this->myLocationIds($period->id);
            $locations = PrakerinLocation::whereIn('id', $myLocIds)->orderBy('name')->get();

            $placements = PrakerinPlacement::with(['student', 'location'])
                ->where('school_id', $this->school()->id)
                ->whereIn('location_id', $myLocIds)
                ->when($locId, fn($q) => $q->where('location_id', $locId))
                ->orderBy('created_at', 'desc')
                ->paginate(30)->withQueryString();

            $students = User::where('school_id', $this->school()->id)
                ->where('role', 'siswa')
                ->where('student_status', 'aktif')
                ->orderBy('name')->get();

            // Siswa yang sudah ditempatkan di periode ini (di DU/DI manapun)
            $placedStudents = PrakerinPlacement::with('location')
                ->where('period_id', $period->id)
                ->where('is_active', true)
                ->get()
                ->keyBy('student_id');
        }

        return view('guru.prakerin.placements', compact(
            'periods', 'period', 'periodId', 'locations', 'placements', 'students', 'locId',
            'placedStudents'
        ));
    }

    public function placementStore(Request $request)
    {
        $data = $request->validate([
            'period_id'   => 'required|exists:prakerin_periods,id',
            'location_id' => 'required|exists:prakerin_locations,id',
            'student_id'  => 'required|exists:users,id',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'notes'       => 'nullable|string',
        ]);

        // Pastikan lokasi yang dipilih adalah lokasi yang dia bimbing
        $myLocIds = $this->myLocationIds((int) $data['period_id']);
        if (! $myLocIds->contains((int) $data['location_id'])) {
            return back()->with('error', 'Anda hanya bisa menempatkan siswa di DU/DI yang Anda bimbing.');
        }

        // Cek apakah siswa sudah ditempatkan di periode ini (di DU/DI manapun)
        $existing = PrakerinPlacement::with('location')
            ->where('period_id', $data['period_id'])
            ->where('student_id', $data['student_id'])
            ->where('is_active', true)
            ->first();

        if ($existing) {
            $lokasiNama = $existing->location->name ?? 'DU/DI lain';
            return back()
                ->withInput()
                ->with('error', "Siswa ini sudah ditempatkan di {$lokasiNama} dalam periode yang sama. Nonaktifkan penempatan lama terlebih dahulu jika ingin memindahkan.");
        }

        $data['school_id'] = $this->school()->id;
        $data['is_active']  = true;
        PrakerinPlacement::create($data);

        return back()->with('success', 'Siswa berhasil ditempatkan.');
    }

    public function placementDestroy(PrakerinPlacement $placement)
    {
        $myLocIds = $this->myLocationIds($placement->period_id);
        abort_unless($myLocIds->contains($placement->location_id), 403);
        $placement->delete();
        return back()->with('success', 'Penempatan dihapus.');
    }

    public function placementShow(PrakerinPlacement $placement)
    {
        // Pembimbing di lokasi ini bisa lihat rekap
        abort_unless(
            $placement->location->supervisors()->where('teacher_id', $this->teacher()->id)->exists(),
            403
        );

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
            $dayAtts = $attendances[$key] ?? collect();
            $days[$key] = [
                'date'     => $current->copy(),
                'checkin'  => $dayAtts->firstWhere('type', 'check_in'),
                'checkout' => $dayAtts->firstWhere('type', 'check_out'),
                'journal'  => $journals[$key] ?? null,
            ];
            $current->addDay();
        }

        return view('guru.prakerin.placement-show', compact('placement', 'days'));
    }

    // ── Rekap ─────────────────────────────────────────────────────────────

    public function recapAbsensi(Request $request)
    {
        $periods  = $this->myPeriods();
        $periodId = $request->get('period_id', $periods->first()?->id);
        $locId    = $request->get('location_id');
        $period   = $periods->find($periodId);

        $myLocIds     = $period ? $this->myLocationIds($period->id) : collect();
        $allLocations = $period ? PrakerinLocation::where('period_id', $period->id)
            ->where('school_id', $this->school()->id)->orderBy('name')->get() : collect();

        $placements = collect();
        $stats      = [];

        if ($period) {
            $filterLocIds = $locId ? collect([$locId]) : $myLocIds;
            $placements = PrakerinPlacement::with(['student', 'location'])
                ->where('school_id', $this->school()->id)
                ->where('period_id', $period->id)
                ->whereIn('location_id', $filterLocIds)
                ->where('is_active', true)
                ->get();

            foreach ($placements as $p) {
                $start     = $p->start_date ?? $period->start_date;
                $end       = min($p->end_date ?? $period->end_date, today());
                $totalDays = $start && $end ? $start->diffInDays($end) + 1 : 0;
                $checkins  = PrakerinAttendance::where('placement_id', $p->id)
                    ->where('type', 'check_in')->get();
                $stats[$p->id] = [
                    'total_days' => $totalDays,
                    'hadir'      => $checkins->whereIn('status', ['hadir', 'terlambat'])->count(),
                    'terlambat'  => $checkins->where('status', 'terlambat')->count(),
                    'alfa'       => $totalDays - $checkins->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit'])->count(),
                    'jurnal'     => PrakerinJournal::where('placement_id', $p->id)->where('status', 'submitted')->count(),
                ];
            }
        }

        return view('guru.prakerin.recap-absensi', compact(
            'periods', 'period', 'periodId', 'allLocations', 'locId', 'placements', 'stats', 'myLocIds'
        ));
    }

    public function recapJurnal(Request $request)
    {
        $periods  = $this->myPeriods();
        $periodId = $request->get('period_id', $periods->first()?->id);
        $locId    = $request->get('location_id');
        $period   = $periods->find($periodId);

        $myLocIds     = $period ? $this->myLocationIds($period->id) : collect();
        $allLocations = $period ? PrakerinLocation::where('period_id', $period->id)
            ->where('school_id', $this->school()->id)->orderBy('name')->get() : collect();

        $journals = collect();
        if ($period) {
            $filterLocIds = $locId ? collect([$locId]) : $myLocIds;
            $journals = PrakerinJournal::with(['student', 'placement.location', 'photos'])
                ->whereHas('placement', fn($q) => $q
                    ->where('school_id', $this->school()->id)
                    ->where('period_id', $period->id)
                    ->whereIn('location_id', $filterLocIds)
                )
                ->where('status', 'submitted')
                ->orderByDesc('journal_date')
                ->paginate(20)->withQueryString();
        }

        return view('guru.prakerin.recap-jurnal', compact(
            'periods', 'period', 'periodId', 'allLocations', 'locId', 'journals', 'myLocIds'
        ));
    }

    public function addNote(PrakerinJournal $journal, Request $request)
    {
        $myLocIds = $this->myLocationIds($journal->placement->period_id);
        abort_unless($myLocIds->contains($journal->placement->location_id), 403);

        $request->validate(['teacher_note' => 'required|string|max:1000']);
        $journal->update([
            'teacher_note' => $request->teacher_note,
            'noted_by'     => Auth::id(),
            'noted_at'     => now(),
        ]);
        return back()->with('success', 'Catatan disimpan.');
    }
}
