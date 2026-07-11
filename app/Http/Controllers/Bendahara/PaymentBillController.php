<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PaymentAuditLog;
use App\Models\PaymentBill;
use App\Models\PaymentInstallment;
use App\Models\PaymentRate;
use App\Models\PaymentTransaction;
use App\Models\PaymentType;
use App\Models\StudentDiscount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentBillController extends Controller
{
    private function school()
    {
        return auth()->user()->school;
    }

    public function index(Request $request)
    {
        $school = $this->school();

        $query = PaymentBill::where('school_id', $school->id)
            ->with(['student', 'paymentType', 'academicYear'])
            ->orderByDesc('period_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('payment_type_id', $request->type);
        }
        if ($request->filled('year')) {
            $query->where('academic_year_id', $request->year);
        }
        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%")
            );
        }

        $bills         = $query->paginate(25)->withQueryString();
        $types         = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();

        return view('bendahara.bills.index', compact('bills', 'types', 'academicYears'));
    }

    public function create()
    {
        $school = $this->school();

        $types         = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();
        $classrooms    = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with(['major', 'students' => fn($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('bendahara.bills.create', compact('types', 'academicYears', 'classrooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_type_id'   => 'required|exists:payment_types,id',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'scope'             => 'required|in:classroom,student',
            'classroom_id'      => 'required_if:scope,classroom|nullable|exists:classrooms,id',
            'student_ids'       => 'required_if:scope,student|nullable|array',
            'student_ids.*'     => 'exists:users,id',
            'period_label'      => 'required|string|max:50',
            'period_date'       => 'required|date',
            'due_date'          => 'nullable|date',
            'installment_type'  => 'required|in:full,installment',
            'installment_count' => 'required_if:installment_type,installment|nullable|integer|min:2|max:12',
        ]);

        $school = $this->school();
        $type   = PaymentType::findOrFail($data['payment_type_id']);

        if ($type->school_id !== $school->id) abort(403);

        // Kumpulkan siswa
        if ($data['scope'] === 'classroom') {
            $classroom = Classroom::findOrFail($data['classroom_id']);
            $students  = $classroom->students()->where('is_active', true)->get();
        } else {
            $students = User::whereIn('id', $data['student_ids'])->get();
        }

        $created = 0;
        $skipped = 0;

        foreach ($students as $student) {
            // Cegah duplikat
            $exists = PaymentBill::where('user_id', $student->id)
                ->where('payment_type_id', $type->id)
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('period_date', $data['period_date'])
                ->exists();

            if ($exists) { $skipped++; continue; }

            // Cari tarif
            $rate       = $this->resolveRate($type->id, $data['academic_year_id'], $student, $school->id);
            $baseAmount = $rate ? $rate->amount : 0;

            // Cari diskon
            $discount    = $this->resolveDiscount($student->id, $type->id, $data['academic_year_id'], $data['period_date']);
            $discountAmt = $this->calcDiscount($baseAmount, $discount);
            $billed      = max(0, $baseAmount - $discountAmt);

            $bill = PaymentBill::create([
                'school_id'        => $school->id,
                'user_id'          => $student->id,
                'payment_type_id'  => $type->id,
                'academic_year_id' => $data['academic_year_id'],
                'payment_rate_id'  => $rate?->id,
                'period_label'     => $data['period_label'],
                'period_date'      => $data['period_date'],
                'amount_base'      => $baseAmount,
                'amount_discount'  => $discountAmt,
                'amount_billed'    => $billed,
                'amount_paid'      => 0,
                'status'           => 'unpaid',
                'due_date'         => $data['due_date'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            // Buat cicilan
            if ($data['installment_type'] === 'installment' && $billed > 0) {
                $count  = (int) $data['installment_count'];
                $each   = intdiv($billed, $count);
                $remain = $billed - ($each * $count);

                for ($i = 1; $i <= $count; $i++) {
                    PaymentInstallment::create([
                        'payment_bill_id'    => $bill->id,
                        'school_id'          => $school->id,
                        'installment_number' => $i,
                        'amount_due'         => $i === 1 ? $each + $remain : $each,
                        'amount_paid'        => 0,
                        'due_date'           => now()->addMonths($i - 1)->startOfMonth(),
                        'status'             => 'unpaid',
                    ]);
                }
            }

            PaymentAuditLog::create([
                'school_id'   => $school->id,
                'user_id'     => auth()->id(),
                'action'      => 'bill_created',
                'target_type' => 'PaymentBill',
                'target_id'   => $bill->id,
                'new_values'  => ['student_id' => $student->id, 'amount_billed' => $billed],
                'ip_address'  => $request->ip(),
            ]);

            $created++;
        }

        $msg = "Tagihan berhasil dibuat untuk {$created} siswa.";
        if ($skipped > 0) $msg .= " {$skipped} siswa dilewati (tagihan sudah ada).";

        return redirect()->route('bendahara.bills.index')->with('success', $msg);
    }

    public function show(PaymentBill $bill)
    {
        $this->authorize($bill);

        $bill->load([
            'student', 'paymentType', 'academicYear',
            'installments',
            'transactions.confirmedBy',
            'transactions.createdBy',
        ]);

        return view('bendahara.bills.show', compact('bill'));
    }

    public function storeCash(Request $request, PaymentBill $bill)
    {
        $this->authorize($bill);

        $request->validate([
            'amount'          => 'required|integer|min:1',
            'installment_id'  => 'nullable|exists:payment_installments,id',
            'cashier_notes'   => 'nullable|string|max:255',
        ]);

        if (in_array($bill->status, ['paid', 'waived'])) {
            return back()->withErrors(['amount' => 'Tagihan ini sudah lunas atau dibebaskan.']);
        }

        $amount = (int) $request->amount;

        if ($amount > $bill->amount_remaining) {
            return back()->withErrors(['amount' => 'Jumlah melebihi sisa tagihan Rp ' . number_format($bill->amount_remaining, 0, ',', '.') . '.']);
        }

        $school = $this->school();

        $trx = PaymentTransaction::create([
            'school_id'              => $school->id,
            'payment_bill_id'        => $bill->id,
            'payment_installment_id' => $request->installment_id,
            'user_id'                => $bill->user_id,
            'reference_number'       => 'CASH-' . strtoupper(Str::random(10)),
            'amount'                 => $amount,
            'channel'                => 'cash',
            'status'                 => 'approved',
            'cashier_notes'          => $request->cashier_notes,
            'confirmed_by'           => auth()->id(),
            'confirmed_at'           => now(),
            'created_by'             => auth()->id(),
            'created_by_ip'          => $request->ip(),
        ]);

        if ($request->installment_id) {
            $inst = PaymentInstallment::find($request->installment_id);
            if ($inst) {
                $inst->amount_paid += $amount;
                $inst->status = $inst->amount_paid >= $inst->amount_due ? 'paid' : 'partial';
                $inst->save();
            }
        }

        $bill->recalculateStatus();

        PaymentAuditLog::create([
            'school_id'   => $school->id,
            'user_id'     => auth()->id(),
            'action'      => 'cash_payment_recorded',
            'target_type' => 'PaymentTransaction',
            'target_id'   => $trx->id,
            'new_values'  => ['amount' => $amount, 'channel' => 'cash'],
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Pembayaran tunai Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dicatat.');
    }

    public function waive(Request $request, PaymentBill $bill)
    {
        $this->authorize($bill);

        $request->validate(['reason' => 'required|string|max:255']);

        if ($bill->status === 'paid') {
            return back()->withErrors(['reason' => 'Tagihan yang sudah lunas tidak dapat dibebaskan.']);
        }

        $old = $bill->status;
        $bill->update(['status' => 'waived']);

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'bill_waived',
            'target_type' => 'PaymentBill',
            'target_id'   => $bill->id,
            'old_values'  => ['status' => $old],
            'new_values'  => ['status' => 'waived', 'reason' => $request->reason],
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Tagihan berhasil dibebaskan.');
    }

    public function edit(PaymentBill $bill)
    {
        $this->authorize($bill);

        if ($bill->amount_paid > 0) {
            return redirect()->route('bendahara.bills.show', $bill)
                ->withErrors(['edit' => 'Tagihan yang sudah ada pembayaran tidak dapat diedit.']);
        }

        $types         = PaymentType::where('school_id', $this->school()->id)->where('is_active', true)->get();
        $academicYears = AcademicYear::where('school_id', $this->school()->id)->orderByDesc('is_active')->get();

        return view('bendahara.bills.edit', compact('bill', 'types', 'academicYears'));
    }

    public function update(Request $request, PaymentBill $bill)
    {
        $this->authorize($bill);

        if ($bill->amount_paid > 0) {
            return back()->withErrors(['edit' => 'Tagihan yang sudah ada pembayaran tidak dapat diedit.']);
        }

        $data = $request->validate([
            'period_label'    => 'required|string|max:50',
            'period_date'     => 'required|date',
            'due_date'        => 'nullable|date',
            'amount_billed'   => 'required|integer|min:0',
            'amount_discount' => 'nullable|integer|min:0',
        ]);

        $data['amount_discount'] = $data['amount_discount'] ?? 0;
        $data['amount_billed']   = max(0, (int)$data['amount_billed'] - (int)$data['amount_discount']);

        $old = $bill->toArray();
        $bill->update($data);

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'bill_updated',
            'target_type' => 'PaymentBill',
            'target_id'   => $bill->id,
            'old_values'  => $old,
            'new_values'  => $data,
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('bendahara.bills.show', $bill)
            ->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function destroy(Request $request, PaymentBill $bill)
    {
        $this->authorize($bill);

        // Keamanan: tidak boleh hapus kalau sudah ada pembayaran approved
        if ($bill->transactions()->where('status', 'approved')->exists()) {
            return back()->withErrors(['delete' => 'Tagihan yang sudah ada pembayaran tidak dapat dihapus.']);
        }

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'bill_deleted',
            'target_type' => 'PaymentBill',
            'target_id'   => $bill->id,
            'old_values'  => $bill->toArray(),
            'ip_address'  => $request->ip(),
        ]);

        // Hapus installments dan transactions pending dulu
        $bill->transactions()->where('status', 'pending')->delete();
        $bill->installments()->delete();
        $bill->delete();

        return redirect()->route('bendahara.bills.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveRate(int $typeId, int $yearId, User $student, int $schoolId): ?PaymentRate
    {
        $classroom   = $student->classrooms()->whereHas('academicYear', fn($q) => $q->where('id', $yearId))->first();
        $majorId     = $classroom?->major_id;
        $classroomId = $classroom?->id;

        foreach ([
            ['classroom_id' => $classroomId, 'major_id' => $majorId],
            ['classroom_id' => null,          'major_id' => $majorId],
            ['classroom_id' => $classroomId,  'major_id' => null],
            ['classroom_id' => null,          'major_id' => null],
        ] as $c) {
            $rate = PaymentRate::where('payment_type_id', $typeId)
                ->where('academic_year_id', $yearId)
                ->where('school_id', $schoolId)
                ->where('classroom_id', $c['classroom_id'])
                ->where('major_id', $c['major_id'])
                ->where('is_active', true)
                ->first();

            if ($rate) return $rate;
        }

        return null;
    }

    private function resolveDiscount(int $userId, int $typeId, int $yearId, string $date): ?StudentDiscount
    {
        return StudentDiscount::where('user_id', $userId)
            ->where('academic_year_id', $yearId)
            ->where(fn($q) => $q->whereNull('payment_type_id')->orWhere('payment_type_id', $typeId))
            ->where('valid_from', '<=', $date)
            ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $date))
            ->orderByDesc('discount_value')
            ->first();
    }

    private function calcDiscount(int $base, ?StudentDiscount $discount): int
    {
        if (!$discount || $base === 0) return 0;
        if ($discount->discount_type === 'percent') {
            return (int) round($base * ($discount->discount_value / 100));
        }
        return min($base, (int) $discount->discount_value);
    }

    private function authorize(PaymentBill $bill)
    {
        if ($bill->school_id !== $this->school()->id) abort(403);
    }
}