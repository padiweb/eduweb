<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\DiscountProgram;
use App\Models\DiscountProgramMember;
use App\Models\PaymentType;
use App\Models\School;
use App\Models\StudentDiscount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DiscountProgramController extends Controller
{
    private function school(): School
    {
        return auth()->user()->school;
    }

    public function index(Request $request)
    {
        $school   = $this->school();
        $programs = DiscountProgram::where('school_id', $school->id)
            ->with(['academicYear', 'paymentType'])
            ->withCount('members')
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        // Hitung jumlah StudentDiscount yang sudah dibuat per program
        // Gunakan discount_program_id jika kolom sudah ada, fallback ke nama
        $appliedCounts = [];
        $hasColumn = Schema::hasColumn('student_discounts', 'discount_program_id');

        foreach ($programs as $program) {
            if ($hasColumn) {
                $appliedCounts[$program->id] = StudentDiscount::where('school_id', $school->id)
                    ->where('discount_program_id', $program->id)
                    ->count();
            } else {
                // Fallback: cek berdasarkan nama program + tahun ajaran
                $appliedCounts[$program->id] = StudentDiscount::where('school_id', $school->id)
                    ->where('name', $program->name)
                    ->where('academic_year_id', $program->academic_year_id)
                    ->count();
            }
        }

        $types         = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();

        return view('bendahara.discounts.programs', compact(
            'programs', 'appliedCounts', 'types', 'academicYears'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'nullable|string|max:30',
            'academic_year_id' => 'required|exists:academic_years,id',
            'payment_type_id'  => 'nullable|exists:payment_types,id',
            'discount_type'    => 'required|in:percent,fixed',
            'default_value'    => 'required|integer|min:0',
            'valid_from'       => 'required|date',
            'valid_until'      => 'nullable|date|after_or_equal:valid_from',
            'description'      => 'nullable|string|max:255',
        ]);

        $data['school_id']  = $this->school()->id;
        $data['created_by'] = auth()->id();
        $data['is_active']  = true;

        DiscountProgram::create($data);
        return back()->with('success', 'Program beasiswa berhasil dibuat.');
    }

    public function update(Request $request, DiscountProgram $program)
    {
        if ($program->school_id !== $this->school()->id) abort(403);
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'nullable|string|max:30',
            'default_value' => 'required|integer|min:0',
            'valid_until'   => 'nullable|date',
            'description'   => 'nullable|string|max:255',
        ]);
        $program->update($data);
        return back()->with('success', 'Program diperbarui.');
    }

    public function toggle(DiscountProgram $program)
    {
        if ($program->school_id !== $this->school()->id) abort(403);
        $program->update(['is_active' => !$program->is_active]);
        return back()->with('success', 'Status program diperbarui.');
    }

    public function members(DiscountProgram $program)
    {
        if ($program->school_id !== $this->school()->id) abort(403);
        $program->load(['members.student', 'academicYear', 'paymentType']);

        $memberIds   = $program->members->pluck('user_id');
        $allStudents = User::where('school_id', $this->school()->id)
            ->where('role', 'siswa')
            ->where('is_active', true)
            ->whereNotIn('id', $memberIds)
            ->orderBy('name')
            ->get();

        return view('bendahara.discounts.members', compact('program', 'allStudents'));
    }

    public function addMembers(Request $request, DiscountProgram $program)
    {
        if ($program->school_id !== $this->school()->id) abort(403);
        $data = $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'overrides'     => 'nullable|array',
        ]);

        $added = 0;
        foreach ($data['student_ids'] as $userId) {
            $override = isset($data['overrides'][$userId]) && $data['overrides'][$userId] !== ''
                ? (int) $data['overrides'][$userId]
                : null;

            DiscountProgramMember::firstOrCreate(
                ['discount_program_id' => $program->id, 'user_id' => $userId],
                ['override_value' => $override, 'created_by' => auth()->id()]
            );
            $added++;
        }

        return back()->with('success', "$added siswa berhasil ditambahkan ke program {$program->name}.");
    }

    public function updateMember(Request $request, DiscountProgramMember $member)
    {
        if ($member->program->school_id !== $this->school()->id) abort(403);
        $request->validate(['override_value' => 'nullable|integer|min:0']);
        $member->update([
            'override_value' => $request->filled('override_value') ? (int)$request->override_value : null,
            'notes'          => $request->notes,
        ]);
        return back()->with('success', 'Override nominal diperbarui.');
    }

    public function removeMember(DiscountProgramMember $member)
    {
        if ($member->program->school_id !== $this->school()->id) abort(403);
        $member->delete();
        return back()->with('success', 'Siswa dihapus dari program.');
    }

    public function apply(Request $request, DiscountProgram $program)
    {
        if ($program->school_id !== $this->school()->id) abort(403);

        $program->load('members');
        $applied    = 0;
        $hasColumn  = Schema::hasColumn('student_discounts', 'discount_program_id');

        foreach ($program->members as $member) {
            $value = $member->override_value ?? $program->default_value;

            $uniqueKey = [
                'school_id'        => $this->school()->id,
                'user_id'          => $member->user_id,
                'academic_year_id' => $program->academic_year_id,
                'name'             => $program->name,
            ];

            // Tambah payment_type_id ke unique key jika ada
            if ($program->payment_type_id) {
                $uniqueKey['payment_type_id'] = $program->payment_type_id;
            }

            $fillData = [
                'discount_type'  => $program->discount_type,
                'discount_value' => $value,
                'valid_from'     => $program->valid_from,
                'valid_until'    => $program->valid_until,
                'notes'          => $member->notes,
                'created_by'     => auth()->id(),
            ];

            // Tambah discount_program_id jika kolom sudah ada
            if ($hasColumn) {
                $uniqueKey['discount_program_id'] = $program->id;
            }

            StudentDiscount::updateOrCreate($uniqueKey, $fillData);
            $applied++;
        }

        return back()->with('success', "Beasiswa diterapkan ke $applied siswa.");
    }

    public function searchStudents(Request $request, DiscountProgram $program)
    {
        if ($program->school_id !== $this->school()->id) abort(403);

        $q         = $request->q ?? '';
        $memberIds = $program->members()->pluck('user_id');

        $students = User::where('school_id', $this->school()->id)
            ->where('role', 'siswa')
            ->whereNotIn('id', $memberIds)
            ->where(fn($q2) =>
                $q2->where('name', 'like', "%$q%")
                   ->orWhere('nis', 'like', "%$q%")
            )
            ->with(['classrooms' => fn($q) =>
                $q->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ])
            ->limit(15)
            ->get()
            ->map(fn($s) => [
                'id'        => $s->id,
                'name'      => $s->name,
                'nis'       => $s->nis ?? '-',
                'classroom' => $s->classrooms->first()?->name ?? '-',
            ]);

        return response()->json($students);
    }
}
