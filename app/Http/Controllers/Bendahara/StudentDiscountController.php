<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PaymentAuditLog;
use App\Models\PaymentType;
use App\Models\StudentDiscount;
use App\Models\User;
use Illuminate\Http\Request;

class StudentDiscountController extends Controller
{
    private function school()
    {
        return auth()->user()->school;
    }

    public function index(Request $request)
    {
        $school = $this->school();

        $query = StudentDiscount::where('school_id', $school->id)
            ->with(['student', 'paymentType', 'academicYear', 'createdBy'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%")
            );
        }

        if ($request->filled('year')) {
            $query->where('academic_year_id', $request->year);
        }

        if ($request->filled('active')) {
            $today = now()->toDateString();
            $query->where('valid_from', '<=', $today)
                  ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today));
        }

        $discounts     = $query->paginate(20)->withQueryString();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();
        $types         = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();

        // Untuk form tambah
        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with(['major', 'students' => fn($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('bendahara.discounts.index', compact(
            'discounts', 'academicYears', 'types', 'classrooms'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'payment_type_id'  => 'nullable|exists:payment_types,id',
            'name'             => 'required|string|max:100',
            'discount_type'    => 'required|in:percent,fixed',
            'discount_value'   => 'required|integer|min:1',
            'valid_from'       => 'required|date',
            'valid_until'      => 'nullable|date|after_or_equal:valid_from',
            'notes'            => 'nullable|string|max:500',
        ]);

        $school = $this->school();

        // Validasi: siswa harus milik sekolah ini
        $student = User::findOrFail($data['user_id']);
        if ($student->school_id !== $school->id) abort(403);

        // Validasi: persen tidak boleh lebih dari 100
        if ($data['discount_type'] === 'percent' && $data['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Diskon persen tidak boleh lebih dari 100%.']);
        }

        $discount = StudentDiscount::create([
            'school_id'        => $school->id,
            'user_id'          => $data['user_id'],
            'academic_year_id' => $data['academic_year_id'],
            'payment_type_id'  => $data['payment_type_id'] ?? null,
            'name'             => $data['name'],
            'discount_type'    => $data['discount_type'],
            'discount_value'   => $data['discount_value'],
            'valid_from'       => $data['valid_from'],
            'valid_until'      => $data['valid_until'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'created_by'       => auth()->id(),
        ]);

        PaymentAuditLog::create([
            'school_id'   => $school->id,
            'user_id'     => auth()->id(),
            'action'      => 'discount_created',
            'target_type' => 'StudentDiscount',
            'target_id'   => $discount->id,
            'new_values'  => $data,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', "Beasiswa/keringanan untuk {$student->name} berhasil ditambahkan.");
    }

    public function destroy(Request $request, StudentDiscount $discount)
    {
        if ($discount->school_id !== $this->school()->id) abort(403);

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'discount_deleted',
            'target_type' => 'StudentDiscount',
            'target_id'   => $discount->id,
            'old_values'  => $discount->toArray(),
            'ip_address'  => $request->ip(),
        ]);

        $discount->delete();

        return back()->with('success', 'Beasiswa/keringanan berhasil dihapus.');
    }

    // Cari siswa via AJAX untuk form tambah
    public function searchStudent(Request $request)
    {
        $school = $this->school();
        $q      = $request->get('q', '');

        $students = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->where('is_active', true)
            ->where(fn($query) =>
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('nis', 'like', "%{$q}%")
            )
            ->with(['classrooms' => fn($q) => $q->whereHas('academicYear', fn($q) => $q->where('is_active', true))->with('major')])
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id'        => $s->id,
                'name'      => $s->name,
                'nis'       => $s->nis ?? '-',
                'classroom' => $s->classrooms->first()?->name ?? '-',
                'major'     => $s->classrooms->first()?->major?->name ?? '-',
            ]);

        return response()->json($students);
    }
}
