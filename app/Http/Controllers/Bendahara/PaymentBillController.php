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
use App\Models\StudentRateOverride;
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

        $yearId = $request->filled('year')
            ? (int) $request->year
            : AcademicYear::where('school_id', $school->id)->where('is_active', true)->value('id');

        $query = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->whereHas('paymentBills', fn($q) =>
                $q->where('school_id', $school->id)
                  ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            )
            ->with(['classrooms' => fn($q) =>
                $q->whereHas('academicYear', fn($q) => $q->where('is_active', true))->with('major')
            ])
            ->orderByDesc(
                PaymentBill::select('created_at')
                    ->whereColumn('user_id', 'users.id')
                    ->where('school_id', $school->id)
                    ->latest()->limit(1)
            );

        if ($request->filled('search')) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%")
            );
        }

        $students      = $query->paginate(20)->withQueryString();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();
        $types         = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();

        $studentIds    = $students->pluck('id');
        $billSummaries = PaymentBill::where('school_id', $school->id)
            ->whereIn('user_id', $studentIds)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->selectRaw('user_id,
                COUNT(*) as total_bills,
                SUM(amount_billed) as total_billed,
                SUM(amount_paid) as total_paid,
                SUM(amount_billed - amount_paid) as total_remaining')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        return view('bendahara.bills.index', compact(
            'students', 'billSummaries', 'academicYears', 'types', 'yearId'
        ));
    }

    // Generate SPP otomatis untuk semua siswa aktif — 1 klik
    public function generateSpp(Request $request)
    {
        $data = $request->validate([
            'payment_type_id'  => 'required|exists:payment_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'period_label'     => 'required|string|max:50',
            'period_date'      => 'required|date',
            'due_date'         => 'nullable|date',
        ]);

        $school = $this->school();
        $type   = PaymentType::findOrFail($data['payment_type_id']);
        $year   = AcademicYear::findOrFail($data['academic_year_id']);

        // Ambil semua siswa aktif di sekolah ini
        $students = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->where('is_active', true)
            ->get();

        $created  = 0;
        $skipped  = 0;

        foreach ($students as $student) {
            // Skip jika tagihan periode ini sudah ada
            $exists = PaymentBill::where('school_id', $school->id)
                ->where('user_id', $student->id)
                ->where('payment_type_id', $type->id)
                ->where('academic_year_id', $year->id)
                ->where('period_label', $data['period_label'])
                ->exists();

            if ($exists) { $skipped++; continue; }

            // Resolusi tarif
            $resolved   = $this->resolveBaseAmount($type->id, $year->id, $student, $school->id);
            $baseAmount = $resolved['amount'];
            $rate       = $resolved['rate'];

            $discount    = $this->resolveDiscount($student->id, $type->id, $year->id, $data['period_date']);
            $discountAmt = $this->calcDiscount($baseAmount, $discount);
            $billed      = max(0, $baseAmount - $discountAmt);

            PaymentBill::create([
                'school_id'        => $school->id,
                'user_id'          => $student->id,
                'payment_type_id'  => $type->id,
                'academic_year_id' => $year->id,
                'payment_rate_id'  => $rate?->id,
                'period_label'     => $data['period_label'],
                'period_date'      => $data['period_date'],
                'due_date'         => $data['due_date'] ?? null,
                'amount_base'      => $baseAmount,
                'amount_discount'  => $discountAmt,
                'amount_billed'    => $billed,
                'amount_paid'      => 0,
                'status'           => 'unpaid',
                'installment_type' => 'full',
                'created_by'       => auth()->id(),
            ]);
            $created++;
        }

        $msg = "SPP berhasil di-generate: $created siswa.";
        if ($skipped > 0) $msg .= " $skipped siswa dilewati (sudah ada tagihan).";

        return back()->with('success', $msg);
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

        $students = $data['scope'] === 'classroom'
            ? Classroom::findOrFail($data['classroom_id'])->students()->where('is_active', true)->get()
            : User::whereIn('id', $data['student_ids'])->get();

        $created = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $exists = PaymentBill::where('user_id', $student->id)
                ->where('payment_type_id', $type->id)
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('period_date', $data['period_date'])
                ->exists();

            if ($exists) { $skipped++; continue; }

            $rate        = $this->resolveRate($type->id, $data['academic_year_id'], $student, $school->id);
            $baseAmount  = $rate ? $rate->amount : 0;
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
                'amount_remaining' => $billed,
                'status'           => 'unpaid',
                'due_date'         => $data['due_date'] ?? null,
                'created_by'       => auth()->id(),
            ]);

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
        $bill->load(['student', 'paymentType', 'academicYear', 'installments',
            'transactions.confirmedBy', 'transactions.createdBy']);

        return view('bendahara.bills.show', compact('bill'));
    }

    // Daftar tunggakan — siswa yang masih punya sisa tagihan
    public function tunggakan(Request $request)
    {
        $school = $this->school();

        $yearId = $request->filled('year')
            ? (int) $request->year
            : AcademicYear::where('school_id', $school->id)->where('is_active', true)->value('id');

        // Ambil data siswa yang masih punya tunggakan
        $query = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->whereHas('paymentBills', fn($q) =>
                $q->where('school_id', $school->id)
                  ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                  ->whereRaw('amount_billed > amount_paid')
                  ->where('status', '!=', 'waived')
            )
            ->with(['classrooms' => fn($q) =>
                $q->whereHas('academicYear', fn($q) => $q->where('is_active', true))->with('major')
            ])
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%")
            );
        }

        $students      = $query->paginate(25)->withQueryString();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();

        // Ringkasan tunggakan per siswa
        $studentIds    = $students->pluck('id');
        $summaries     = PaymentBill::where('school_id', $school->id)
            ->whereIn('user_id', $studentIds)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->whereRaw('amount_billed > amount_paid')
            ->where('status', '!=', 'waived')
            ->selectRaw('user_id,
                COUNT(*) as jumlah_tagihan,
                SUM(amount_billed - amount_paid) as total_tunggakan')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Total seluruh tunggakan (semua siswa, tidak hanya yang di halaman ini)
        $grandTotal    = PaymentBill::where('school_id', $school->id)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->whereRaw('amount_billed > amount_paid')
            ->where('status', '!=', 'waived')
            ->sum(\DB::raw('amount_billed - amount_paid'));

        return view('bendahara.bills.tunggakan', compact(
            'students', 'summaries', 'academicYears', 'yearId', 'grandTotal'
        ));
    }

    public function studentBills(Request $request, User $student)
    {
        $school = $this->school();
        if ($student->school_id !== $school->id) abort(403);

        $yearId = $request->filled('year')
            ? (int) $request->year
            : AcademicYear::where('school_id', $school->id)->where('is_active', true)->value('id');

        $bills = PaymentBill::where('school_id', $school->id)
            ->where('user_id', $student->id)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->with([
                'paymentType',
                'academicYear',
                'transactions' => fn($q) => $q->where('status','approved')->orderBy('created_at'),
            ])
            ->orderByDesc('created_at')
            ->get();

        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();

        // Beasiswa yang masih bisa dipakai:
        // - Belum expired
        // - Untuk beasiswa WAIVER (potongan): hanya tampil jika ada tagihan yang belum lunas yang sesuai
        // - Untuk beasiswa CASH (dana): bisa dipakai selama ada tagihan yang belum lunas
        $allDiscounts = StudentDiscount::where('user_id', $student->id)
            ->where('school_id', $school->id)
            ->where('valid_from', '<=', now()->toDateString())
            ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString()))
            ->with('paymentType')
            ->get();

        // Cek beasiswa yang sudah pernah dipakai di tagihan siswa ini
        $usedDiscountIds = PaymentTransaction::whereIn('payment_bill_id', $bills->pluck('id'))
            ->where('status', 'approved')
            ->whereIn('channel', ['scholarship_waiver', 'scholarship_cash', 'scholarship'])
            ->pluck('cashier_notes')
            ->map(fn($n) => str_replace('Beasiswa: ', '', $n ?? ''))
            ->toArray();

        // Filter: beasiswa waiver hanya tampil jika belum pernah dipakai di tagihan ini
        // Beasiswa cash bisa dipakai berulang selama nominalnya lebih besar dari tagihan
        $discounts = $allDiscounts->filter(function($disc) use ($usedDiscountIds, $bills) {
            $scholarshipType = $disc->scholarship_type ?? 'cash';

            if ($scholarshipType === 'waiver') {
                // Potongan: cek apakah sudah pernah dipakai untuk tagihan yang sama jenisnya
                // Sudah terpakai jika nama beasiswa ini ada di riwayat transaksi
                $alreadyUsed = in_array($disc->name, $usedDiscountIds);
                if ($alreadyUsed) return false;

                // Juga tidak tampil jika tidak ada tagihan yang belum lunas untuk jenis ini
                $hasUnpaidBill = $bills->filter(function($bill) use ($disc) {
                    if ($bill->amount_remaining <= 0) return false;
                    if ($disc->payment_type_id && $bill->payment_type_id !== $disc->payment_type_id) return false;
                    return true;
                })->isNotEmpty();
                return $hasUnpaidBill;
            }

            // Cash: tampil selama ada tagihan yang belum lunas
            return $bills->where('amount_remaining', '>', 0)->isNotEmpty();
        })->values();

        $totalBilled    = $bills->sum('amount_billed');
        $totalPaid      = $bills->sum('amount_paid');
        $totalRemaining = $bills->sum(fn($b) => $b->amount_remaining);

        return view('bendahara.bills.student', compact(
            'student', 'bills', 'academicYears', 'yearId',
            'discounts', 'totalBilled', 'totalPaid', 'totalRemaining'
        ));
    }

    public function edit(PaymentBill $bill)
    {
        $this->authorize($bill);

        if ($bill->transactions()->where('status', 'approved')->exists()) {
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

        if ($bill->transactions()->where('status', 'approved')->exists()) {
            return back()->withErrors(['edit' => 'Tagihan yang sudah ada pembayaran tidak dapat diedit.']);
        }

        $data = $request->validate([
            'period_label'    => 'required|string|max:50',
            'period_date'     => 'required|date',
            'due_date'        => 'nullable|date',
            'amount_base'     => 'required|integer|min:0',
            'amount_discount' => 'nullable|integer|min:0',
        ]);

        $base     = (int) $data['amount_base'];
        $discount = (int) ($data['amount_discount'] ?? 0);
        $billed   = max(0, $base - $discount);

        $old = $bill->toArray();

        $bill->update([
            'period_label'     => $data['period_label'],
            'period_date'      => $data['period_date'],
            'due_date'         => $data['due_date'] ?? null,
            'amount_base'      => $base,
            'amount_discount'  => $discount,
            'amount_billed'    => $billed,
            'amount_remaining' => max(0, $billed - $bill->amount_paid),
        ]);

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

        $bill->transactions()->where('status', 'pending')->delete();
        $bill->installments()->delete();
        $bill->delete();

        return redirect()->route('bendahara.bills.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    public function storeCash(Request $request, PaymentBill $bill)
    {
        $this->authorize($bill);

        $request->validate([
            'pay_type'      => 'required|in:full,partial,scholarship',
            'amount'        => 'required_if:pay_type,partial|nullable|integer|min:1',
            'discount_id'   => 'required_if:pay_type,scholarship|nullable|exists:student_discounts,id',
            'cashier_notes' => 'nullable|string|max:255',
        ]);

        if (in_array($bill->status, ['paid', 'waived'])) {
            return back()->withErrors(['pay_type' => 'Tagihan sudah lunas atau dibebaskan.']);
        }

        $school  = $this->school();
        $channel = 'cash';
        $notes   = $request->cashier_notes;

        if ($request->pay_type === 'full') {
            $amount = $bill->amount_remaining;
        } elseif ($request->pay_type === 'scholarship') {
            $discount = StudentDiscount::findOrFail($request->discount_id);
            $notes    = 'Beasiswa: ' . $discount->name;

            // Hitung nominal beasiswa — cap di amount_remaining (tidak boleh lebih dari tagihan)
            if ($discount->discount_type === 'percent') {
                $discountAmount = (int) round($bill->amount_billed * $discount->discount_value / 100);
            } else {
                $discountAmount = (int) $discount->discount_value;
            }
            $amount = min($discountAmount, $bill->amount_remaining);

            if ($amount <= 0) {
                return back()->withErrors(['discount_id' => 'Nilai beasiswa tidak valid atau tagihan sudah lunas.']);
            }

            // Dua jenis beasiswa:
            // 'scholarship_cash'   = dana uang (PIP dll) → MASUK pemasukan kas siswa
            // 'scholarship_waiver' = potongan tagihan    → TIDAK masuk pemasukan
            $channel = $discount->scholarship_type === 'cash' ? 'scholarship_cash' : 'scholarship_waiver';
        } else {
            $amount = (int) $request->amount;
            if ($amount <= 0 || $amount > $bill->amount_remaining) {
                return redirect()->route('bendahara.bills.student', [$bill->user_id, 'year' => $bill->academic_year_id])
                    ->withErrors(['amount' => 'Nominal tidak valid.']);
            }
        }

        $trx = PaymentTransaction::create([
            'school_id'              => $school->id,
            'payment_bill_id'        => $bill->id,
            'payment_installment_id' => null,
            'user_id'                => $bill->user_id,
            'reference_number'       => 'CASH-' . strtoupper(Str::random(10)),
            'amount'                 => $amount,
            'channel'                => $channel,
            'status'                 => 'approved',
            'cashier_notes'          => $notes,
            'confirmed_by'           => auth()->id(),
            'confirmed_at'           => now(),
            'created_by'             => auth()->id(),
            'created_by_ip'          => $request->ip(),
        ]);

        $bill->recalculateStatus();

        // Catat ke kas siswa:
        // cash / scholarship_cash → MASUK pemasukan (uang benar-benar diterima)
        // scholarship_waiver      → TIDAK masuk (hanya potongan, bukan uang masuk)
        $shouldRecordIncome = in_array($channel, ['cash', 'scholarship_cash']);

        if ($shouldRecordIncome) {
            $kasSource = \App\Models\FundSource::where('school_id', $school->id)
                ->where('type', 'siswa')->where('is_active', true)->first();
            if ($kasSource) {
                $activeYear = AcademicYear::where('school_id', $school->id)->where('is_active', true)->first();
                \App\Models\FundIncome::create([
                    'school_id'        => $school->id,
                    'fund_source_id'   => $kasSource->id,
                    'academic_year_id' => $activeYear?->id ?? $bill->academic_year_id,
                    'description'      => 'Bayar ' . $bill->paymentType->name . ' - ' . $bill->student->name,
                    'amount'           => $amount,
                    'income_date'      => now()->toDateString(),
                    'period_label'     => $bill->period_label,
                    'reference_number' => $trx->reference_number,
                    'notes'            => str_contains($channel, 'scholarship') ? $notes : null,
                    'created_by'       => auth()->id(),
                ]);
            }
        }

        PaymentAuditLog::create([
            'school_id'   => $school->id,
            'user_id'     => auth()->id(),
            'action'      => 'cash_payment_recorded',
            'target_type' => 'PaymentTransaction',
            'target_id'   => $trx->id,
            'new_values'  => ['amount' => $amount, 'channel' => $channel],
            'ip_address'  => $request->ip(),
        ]);

        $label = $request->pay_type === 'full' ? 'Tagihan lunas' : 'Cicilan Rp ' . number_format($amount, 0, ',', '.');

        if ($request->filled('print_receipt')) {
            // Redirect ke struk transaksi (tab baru ditangani JS di halaman sebelumnya)
            return redirect()->route('bendahara.transactions.receipt', $trx)
                ->with('success', $label . ' berhasil dicatat.');
        }

        return redirect()->route('bendahara.bills.student', [$bill->user_id, 'year' => $bill->academic_year_id])
            ->with('success', $label . ' berhasil dicatat.');
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

    // ── Tambahan ──────────────────────────────────────────────────────────────

    public function checkRate(Request $request)
    {
        $school  = $this->school();
        $typeId  = (int) $request->payment_type_id;
        $yearId  = (int) $request->academic_year_id;
        $scope   = $request->scope;
        $results = [];

        $students = $scope === 'classroom'
            ? Classroom::findOrFail($request->classroom_id)->students()->where('is_active', true)->get()
            : User::whereIn('id', $request->student_ids ?? [])->get();

        foreach ($students as $student) {
            $override = StudentRateOverride::where('school_id', $school->id)
                ->where('user_id', $student->id)
                ->where('payment_type_id', $typeId)
                ->where('academic_year_id', $yearId)
                ->first();

            if ($override) {
                $results[] = ['id'=>$student->id,'name'=>$student->name,'amount'=>$override->amount,'source'=>'override'];
                continue;
            }

            $rate = $this->resolveRate($typeId, $yearId, $student, $school->id);
            $results[] = ['id'=>$student->id,'name'=>$student->name,
                'amount'=>$rate?->amount ?? 0,'source'=>$rate ? 'rate' : 'none'];
        }

        return response()->json([
            'results' => $results,
            'no_rate' => collect($results)->where('source','none')->count(),
        ]);
    }

    public function overrides(Request $request)
    {
        $school        = $this->school();
        $overrides     = StudentRateOverride::where('school_id', $school->id)
            ->with(['student','paymentType','academicYear','createdBy'])
            ->orderByDesc('created_at')->paginate(25)->withQueryString();
        $types         = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();
        return view('bendahara.payment-types.overrides', compact('overrides','types','academicYears'));
    }

    public function storeOverride(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'payment_type_id'  => 'required|exists:payment_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'amount'           => 'required|integer|min:0',
            'reason'           => 'nullable|string|max:255',
        ]);
        $school = $this->school();
        StudentRateOverride::updateOrCreate(
            ['school_id'=>$school->id,'user_id'=>$data['user_id'],
             'payment_type_id'=>$data['payment_type_id'],'academic_year_id'=>$data['academic_year_id']],
            ['amount'=>$data['amount'],'reason'=>$data['reason']??null,'created_by'=>auth()->id()]
        );
        return back()->with('success','Override tarif berhasil disimpan.');
    }

    public function destroyOverride(StudentRateOverride $override)
    {
        if ($override->school_id !== $this->school()->id) abort(403);
        $override->delete();
        return back()->with('success','Override tarif berhasil dihapus.');
    }

    // Kwitansi pembayaran — cetak termal
    // Struk per transaksi — bisa dicetak tiap cicilan
    public function transactionReceipt(\App\Models\PaymentTransaction $transaction)
    {
        $bill   = $transaction->bill;
        $school = $this->school();
        if ($bill->school_id !== $school->id) abort(403);

        $bill->load([
            'student', 'paymentType', 'academicYear',
            'transactions' => fn($q) => $q->where('status','approved')->orderBy('created_at'),
        ]);

        $paymentNumber = $bill->transactions->search(fn($t) => $t->id === $transaction->id) + 1;

        return view('bendahara.bills.transaction-receipt', compact('transaction','bill','school','paymentNumber'));
    }

    public function receipt(PaymentBill $bill)
    {
        $this->authorize($bill);
        $bill->load(['student','paymentType','academicYear',
            'transactions' => fn($q) => $q->where('status','approved')->latest()]);
        $school = $this->school();
        return view('bendahara.bills.receipt', compact('bill','school'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

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
