<?php

namespace App\Http\Controllers\Admin\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PrakerinAttendance;
use App\Models\PrakerinJournal;
use App\Models\PrakerinJournalPhoto;
use App\Models\PrakerinPlacement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrakerinPlacementController extends Controller
{
    private function school()
    {
        return Auth::user()->school;
    }

    /** Daftar semua placement + filter per tahun ajaran */
    public function index(Request $request)
    {
        $school     = $this->school();
        $years      = AcademicYear::where('school_id', $school->id)->orderByDesc('year')->get();
        $activeYear = AcademicYear::where('school_id', $school->id)->where('is_active', true)->first();
        $yearId     = $request->get('year_id', $activeYear?->id);

        $placements = PrakerinPlacement::with(['student', 'supervisorTeacher', 'academicYear'])
            ->where('school_id', $school->id)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->orderBy('company_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.prakerin.placements.index', compact('placements', 'years', 'yearId', 'activeYear'));
    }

    /** Form tambah placement */
    public function create()
    {
        $school   = $this->school();
        $students = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->whereDoesntHave('prakerinPlacements', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->orderBy('name')
            ->get();

        $years = AcademicYear::where('school_id', $school->id)->orderByDesc('year')->get();

        return view('admin.prakerin.placements.create', compact('students', 'teachers', 'years'));
    }

    /** Simpan placement baru */
    public function store(Request $request)
    {
        $school = $this->school();

        $data = $request->validate([
            'student_id'             => 'required|exists:users,id',
            'academic_year_id'       => 'required|exists:academic_years,id',
            'supervisor_teacher_id'  => 'nullable|exists:users,id',
            'company_name'           => 'required|string|max:150',
            'company_address'        => 'nullable|string',
            'latitude'               => 'nullable|numeric|between:-90,90',
            'longitude'              => 'nullable|numeric|between:-180,180',
            'radius_meters'          => 'required|integer|min:50|max:2000',
            'field_supervisor_name'  => 'nullable|string|max:100',
            'field_supervisor_phone' => 'nullable|string|max:20',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after_or_equal:start_date',
        ]);

        $data['school_id'] = $school->id;
        $data['is_active']  = true;

        PrakerinPlacement::create($data);

        return redirect()->route('admin.prakerin.placements.index')
            ->with('success', 'Penempatan prakerin berhasil ditambahkan.');
    }

    /** Form edit */
    public function edit(PrakerinPlacement $placement)
    {
        $school   = $this->school();
        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->orderBy('name')
            ->get();
        $years = AcademicYear::where('school_id', $school->id)->orderByDesc('year')->get();

        return view('admin.prakerin.placements.edit', compact('placement', 'teachers', 'years'));
    }

    /** Update placement */
    public function update(Request $request, PrakerinPlacement $placement)
    {
        $data = $request->validate([
            'supervisor_teacher_id'  => 'nullable|exists:users,id',
            'company_name'           => 'required|string|max:150',
            'company_address'        => 'nullable|string',
            'latitude'               => 'nullable|numeric|between:-90,90',
            'longitude'              => 'nullable|numeric|between:-180,180',
            'radius_meters'          => 'required|integer|min:50|max:2000',
            'field_supervisor_name'  => 'nullable|string|max:100',
            'field_supervisor_phone' => 'nullable|string|max:20',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after_or_equal:start_date',
            'is_active'              => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $placement->update($data);

        return redirect()->route('admin.prakerin.placements.index')
            ->with('success', 'Data penempatan berhasil diperbarui.');
    }

    /** Hapus placement */
    public function destroy(PrakerinPlacement $placement)
    {
        $placement->delete();
        return redirect()->route('admin.prakerin.placements.index')
            ->with('success', 'Data penempatan dihapus.');
    }

    /** Guru tambah catatan di jurnal siswa */
    public function addJournalNote(PrakerinJournal $journal, Request $request)
    {
        $request->validate(['teacher_note' => 'required|string|max:1000']);
        $journal->update([
            'teacher_note' => $request->teacher_note,
            'noted_by'     => Auth::id(),
            'noted_at'     => now(),
        ]);
        return back()->with('success', 'Catatan berhasil disimpan.');
    }

    /** Detail rekap absensi + jurnal per siswa */
    public function show(PrakerinPlacement $placement)
    {
        $placement->load(['student', 'supervisorTeacher', 'academicYear']);

        // Rekap per tanggal
        $attendances = PrakerinAttendance::where('placement_id', $placement->id)
            ->orderBy('attendance_date')
            ->orderBy('type')
            ->get()
            ->groupBy(fn($a) => $a->attendance_date->format('Y-m-d'));

        $journals = PrakerinJournal::with('photos')
            ->where('placement_id', $placement->id)
            ->orderBy('journal_date')
            ->get()
            ->keyBy(fn($j) => $j->journal_date->format('Y-m-d'));

        // Buat list hari kerja dalam periode
        $days = [];
        $current = $placement->start_date->copy();
        while ($current->lte($placement->end_date)) {
            $key = $current->format('Y-m-d');
            $days[$key] = [
                'date'     => $current->copy(),
                'checkin'  => $attendances[$key][0] ?? null,
                'checkout' => $attendances[$key][1] ?? null,
                'journal'  => $journals[$key] ?? null,
            ];
            $current->addDay();
        }

        return view('admin.prakerin.placements.show', compact('placement', 'days'));
    }
}
