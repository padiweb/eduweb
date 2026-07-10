<?php

namespace App\Services;

use App\Models\PaymentBill;
use App\Models\PaymentInstallment;
use App\Models\PaymentTransaction;
use App\Models\PaymentAuditLog;
use App\Models\PaymentRate;
use App\Models\StudentDiscount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Buat tagihan untuk satu siswa.
     * Otomatis hitung diskon dan generate cicilan.
     */
    public function createBill(
        User   $student,
        int    $paymentTypeId,
        int    $academicYearId,
        string $periodLabel,
        string $periodDate,  // format: Y-m-d (tanggal awal periode)
        int    $baseAmount,
        ?int   $paymentRateId = null,
        ?string $dueDate = null,
        int    $installmentCount = 1  // 1 = bayar penuh, >1 = cicilan
    ): PaymentBill {
        return DB::transaction(function () use (
            $student, $paymentTypeId, $academicYearId,
            $periodLabel, $periodDate, $baseAmount,
            $paymentRateId, $dueDate, $installmentCount
        ) {
            $schoolId = $student->school_id;

            // Hitung total diskon dari semua keringanan aktif siswa ini
            $discount = $this->calculateDiscount(
                $student->id, $paymentTypeId, $academicYearId, $baseAmount, $schoolId
            );

            $amountBilled = max(0, $baseAmount - $discount);

            $bill = PaymentBill::create([
                'school_id'       => $schoolId,
                'user_id'         => $student->id,
                'payment_type_id' => $paymentTypeId,
                'academic_year_id'=> $academicYearId,
                'payment_rate_id' => $paymentRateId,
                'period_label'    => $periodLabel,
                'period_date'     => $periodDate,
                'amount_base'     => $baseAmount,
                'amount_discount' => $discount,
                'amount_billed'   => $amountBilled,
                'amount_paid'     => 0,
                'status'          => $amountBilled === 0 ? 'waived' : 'unpaid',
                'due_date'        => $dueDate,
                'created_by'      => auth()->id(),
            ]);

            // Generate jadwal cicilan
            if ($amountBilled > 0 && $installmentCount > 1) {
                $this->generateInstallments($bill, $installmentCount, $dueDate);
            }

            PaymentAuditLog::record('bill_created', $bill, null, $bill->toArray());

            return $bill;
        });
    }

    /**
     * Buat tagihan massal untuk seluruh siswa aktif satu kelas/angkatan.
     * Returns: ['created' => n, 'skipped' => n, 'errors' => [...]]
     */
    public function createBulkBills(
        int    $schoolId,
        int    $paymentTypeId,
        int    $academicYearId,
        string $periodLabel,
        string $periodDate,
        int    $installmentCount = 1,
        ?int   $classroomId = null // null = semua kelas aktif
    ): array {
        $query = User::where('school_id', $schoolId)
                     ->where('role', 'siswa')
                     ->where('student_status', 'aktif');

        if ($classroomId) {
            $query->whereHas('classrooms', fn($q) =>
                $q->where('classrooms.id', $classroomId)
                  ->where('academic_year_id', $academicYearId)
            );
        }

        $students = $query->with('classrooms.major')->get();

        $created = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($students as $student) {
            // Skip jika tagihan periode ini sudah ada
            $exists = PaymentBill::where([
                'user_id'         => $student->id,
                'payment_type_id' => $paymentTypeId,
                'academic_year_id'=> $academicYearId,
                'period_date'     => $periodDate,
            ])->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Cari tarif yang paling spesifik untuk siswa ini
            $rate = $this->findBestRate($student, $paymentTypeId, $academicYearId, $schoolId);

            if (!$rate) {
                $errors[] = "Siswa {$student->name}: tarif tidak ditemukan";
                $skipped++;
                continue;
            }

            try {
                $this->createBill(
                    $student, $paymentTypeId, $academicYearId,
                    $periodLabel, $periodDate,
                    $rate->amount, $rate->id,
                    null, $installmentCount
                );
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Siswa {$student->name}: " . $e->getMessage();
            }
        }

        return compact('created', 'skipped', 'errors');
    }

    /**
     * Input pembayaran tunai oleh bendahara.
     * Status langsung approved.
     */
    public function recordCashPayment(
        PaymentBill $bill,
        int         $amount,
        ?int        $installmentId = null,
        ?string     $notes = null
    ): PaymentTransaction {
        return DB::transaction(function () use ($bill, $amount, $installmentId, $notes) {
            // Validasi: amount tidak boleh melebihi sisa tagihan
            if ($amount > $bill->amount_remaining) {
                throw new \InvalidArgumentException(
                    "Jumlah bayar (Rp " . number_format($amount, 0, ',', '.') . ") " .
                    "melebihi sisa tagihan (Rp " . number_format($bill->amount_remaining, 0, ',', '.') . ")"
                );
            }

            $txn = PaymentTransaction::create([
                'school_id'           => $bill->school_id,
                'payment_bill_id'     => $bill->id,
                'payment_installment_id' => $installmentId,
                'user_id'             => $bill->user_id,
                'reference_number'    => PaymentTransaction::generateReference($bill->school_id),
                'amount'              => $amount,
                'channel'             => 'cash',
                'status'              => 'approved', // Tunai langsung approved
                'cashier_notes'       => $notes,
                'confirmed_by'        => auth()->id(),
                'confirmed_at'        => now(),
                'created_by'          => auth()->id(),
                'created_by_ip'       => request()->ip(),
            ]);

            // Update tagihan
            $bill->recalculateStatus();

            // Update cicilan jika ada
            if ($installmentId) {
                $this->recalculateInstallment($installmentId);
            }

            PaymentAuditLog::record('cash_payment_recorded', $txn, null, [
                'amount'      => $amount,
                'bill_id'     => $bill->id,
                'reference'   => $txn->reference_number,
            ]);

            return $txn;
        });
    }

    /**
     * Upload bukti transfer oleh siswa/ortu.
     * Status pending — menunggu konfirmasi bendahara.
     */
    public function submitTransferProof(
        PaymentBill $bill,
        int         $amount,
        \Illuminate\Http\UploadedFile $receiptFile,
        string      $bankName,
        string      $senderName,
        string      $transferDate,
        ?int        $installmentId = null,
        ?string     $notes = null
    ): PaymentTransaction {
        return DB::transaction(function () use (
            $bill, $amount, $receiptFile, $bankName,
            $senderName, $transferDate, $installmentId, $notes
        ) {
            // Simpan file di private storage (bukan public)
            $path = $receiptFile->store(
                "payment-receipts/{$bill->school_id}/{$bill->user_id}",
                'private'
            );

            $txn = PaymentTransaction::create([
                'school_id'              => $bill->school_id,
                'payment_bill_id'        => $bill->id,
                'payment_installment_id' => $installmentId,
                'user_id'                => $bill->user_id,
                'reference_number'       => PaymentTransaction::generateReference($bill->school_id),
                'amount'                 => $amount,
                'channel'                => 'transfer',
                'status'                 => 'pending',
                'receipt_path'           => $path,
                'bank_name'              => $bankName,
                'sender_name'            => $senderName,
                'transfer_date'          => $transferDate,
                'notes'                  => $notes,
                'created_by'             => auth()->id(),
                'created_by_ip'          => request()->ip(),
            ]);

            PaymentAuditLog::record('transfer_submitted', $txn, null, [
                'amount'    => $amount,
                'bill_id'   => $bill->id,
                'reference' => $txn->reference_number,
            ]);

            return $txn;
        });
    }

    /**
     * Bendahara konfirmasi / tolak bukti transfer.
     */
    public function confirmTransaction(
        PaymentTransaction $txn,
        bool   $approved,
        ?string $reason = null
    ): PaymentTransaction {
        if (!in_array($txn->status, ['pending'])) {
            throw new \InvalidArgumentException('Hanya transaksi berstatus pending yang bisa dikonfirmasi.');
        }

        return DB::transaction(function () use ($txn, $approved, $reason) {
            $oldStatus = $txn->status;

            $txn->status       = $approved ? 'approved' : 'rejected';
            $txn->confirmed_by = auth()->id();
            $txn->confirmed_at = now();

            if (!$approved) {
                $txn->rejection_reason = $reason;
            }

            $txn->save();

            // Update status tagihan (hanya jika approved)
            if ($approved) {
                $txn->bill->recalculateStatus();

                if ($txn->payment_installment_id) {
                    $this->recalculateInstallment($txn->payment_installment_id);
                }
            }

            PaymentAuditLog::record(
                $approved ? 'transfer_approved' : 'transfer_rejected',
                $txn,
                ['status' => $oldStatus],
                ['status' => $txn->status, 'reason' => $reason]
            );

            return $txn;
        });
    }

    /**
     * Batalkan transaksi (dengan alasan wajib).
     */
    public function cancelTransaction(
        PaymentTransaction $txn,
        string $reason
    ): PaymentTransaction {
        if ($txn->status === 'cancelled') {
            throw new \InvalidArgumentException('Transaksi sudah dibatalkan.');
        }

        return DB::transaction(function () use ($txn, $reason) {
            $wasApproved = $txn->status === 'approved';
            $oldStatus   = $txn->status;

            $txn->status               = 'cancelled';
            $txn->cancellation_reason  = $reason;
            $txn->save();

            // Jika sebelumnya approved, recalculate tagihan
            if ($wasApproved) {
                $txn->bill->recalculateStatus();
                if ($txn->payment_installment_id) {
                    $this->recalculateInstallment($txn->payment_installment_id);
                }
            }

            PaymentAuditLog::record(
                'transaction_cancelled',
                $txn,
                ['status' => $oldStatus],
                ['status' => 'cancelled', 'reason' => $reason]
            );

            return $txn;
        });
    }

    /**
     * Bebaskan tagihan (waive) — keputusan khusus bendahara/kepsek.
     */
    public function waiveBill(PaymentBill $bill, string $reason): PaymentBill
    {
        $old = $bill->only(['status', 'amount_billed']);

        $bill->status = 'waived';
        $bill->save();

        PaymentAuditLog::record('bill_waived', $bill, $old, [
            'status' => 'waived',
            'reason' => $reason,
        ], $reason);

        return $bill;
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function calculateDiscount(
        int $userId, int $paymentTypeId, int $academicYearId, int $baseAmount, int $schoolId
    ): int {
        $discounts = StudentDiscount::where('school_id', $schoolId)
            ->where('user_id', $userId)
            ->where('academic_year_id', $academicYearId)
            ->where(fn($q) =>
                $q->whereNull('payment_type_id')
                  ->orWhere('payment_type_id', $paymentTypeId)
            )
            ->activeNow()
            ->get();

        $total = 0;
        foreach ($discounts as $discount) {
            $total += $discount->calculateDiscount($baseAmount);
        }

        // Total diskon tidak boleh melebihi 100% tagihan
        return min($total, $baseAmount);
    }

    private function findBestRate(User $student, int $paymentTypeId, int $academicYearId, int $schoolId): ?PaymentRate
    {
        // Cari kelas aktif siswa
        $classroom = $student->classrooms()
            ->where('academic_year_id', $academicYearId)
            ->first();

        $classroomId = $classroom?->id;
        $majorId     = $classroom?->major_id;

        // Prioritas: kelas+jurusan > kelas > jurusan > semua
        $candidates = [
            ['classroom_id' => $classroomId, 'major_id' => $majorId],
            ['classroom_id' => $classroomId, 'major_id' => null],
            ['classroom_id' => null,          'major_id' => $majorId],
            ['classroom_id' => null,          'major_id' => null],
        ];

        foreach ($candidates as $cond) {
            $rate = PaymentRate::where('payment_type_id', $paymentTypeId)
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $academicYearId)
                ->where('classroom_id', $cond['classroom_id'])
                ->where('major_id', $cond['major_id'])
                ->where('is_active', true)
                ->first();

            if ($rate) return $rate;
        }

        return null;
    }

    private function generateInstallments(PaymentBill $bill, int $count, ?string $firstDueDate): void
    {
        $totalAmount = $bill->amount_billed;
        $baseAmount  = intdiv($totalAmount, $count);
        $remainder   = $totalAmount - ($baseAmount * $count);

        $dueDate = $firstDueDate ? Carbon::parse($firstDueDate) : now()->addMonth();

        for ($i = 1; $i <= $count; $i++) {
            // Sisa pembulatan ditambahkan ke cicilan terakhir
            $amount = $i === $count ? $baseAmount + $remainder : $baseAmount;

            PaymentInstallment::create([
                'payment_bill_id'    => $bill->id,
                'school_id'          => $bill->school_id,
                'installment_number' => $i,
                'amount_due'         => $amount,
                'amount_paid'        => 0,
                'due_date'           => $dueDate->copy(),
                'status'             => 'unpaid',
            ]);

            $dueDate->addMonth();
        }
    }

    private function recalculateInstallment(int $installmentId): void
    {
        $installment = PaymentInstallment::find($installmentId);
        if (!$installment) return;

        $paid = $installment->transactions()
            ->where('status', 'approved')
            ->sum('amount');

        $installment->amount_paid = (int) $paid;
        $installment->status = match(true) {
            $paid <= 0                          => 'unpaid',
            $paid >= $installment->amount_due   => 'paid',
            default                             => 'partial',
        };
        $installment->save();
    }

    /**
     * Generate URL sementara untuk melihat bukti transfer (private storage).
     * URL expired setelah 5 menit.
     */
    public function getReceiptUrl(PaymentTransaction $txn): ?string
    {
        if (!$txn->receipt_path) return null;

        return route('payment.receipt', [
            'txn'       => $txn->id,
            'signature' => hash_hmac('sha256', $txn->id . '|' . $txn->receipt_path, config('app.key')),
        ]);
    }
}
