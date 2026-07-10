<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentBill extends Model
{
    protected $fillable = [
        'school_id', 'user_id', 'payment_type_id', 'academic_year_id',
        'payment_rate_id', 'period_label', 'period_date',
        'amount_base', 'amount_discount', 'amount_billed', 'amount_paid',
        'status', 'due_date', 'created_by',
    ];

    protected $casts = [
        'period_date'     => 'date',
        'due_date'        => 'date',
        'amount_base'     => 'integer',
        'amount_discount' => 'integer',
        'amount_billed'   => 'integer',
        'amount_paid'     => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function paymentRate(): BelongsTo
    {
        return $this->belongsTo(PaymentRate::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentInstallment::class)->orderBy('installment_number');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // Sisa yang belum dibayar
    public function getAmountRemainingAttribute(): int
    {
        return max(0, $this->amount_billed - $this->amount_paid);
    }

    // Format helpers
    public function getAmountBilledFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount_billed, 0, ',', '.');
    }

    public function getAmountPaidFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount_paid, 0, ',', '.');
    }

    public function getAmountRemainingFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount_remaining, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'unpaid'  => 'Belum bayar',
            'partial' => 'Cicilan',
            'paid'    => 'Lunas',
            'waived'  => 'Dibebaskan',
            default   => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'unpaid'  => 'red',
            'partial' => 'yellow',
            'paid'    => 'green',
            'waived'  => 'blue',
            default   => 'gray',
        };
    }

    // Recalculate dan update status setelah ada transaksi baru
    public function recalculateStatus(): void
    {
        // Hitung total dari approved transactions saja
        $paid = $this->transactions()
                     ->where('status', 'approved')
                     ->sum('amount');

        $this->amount_paid = (int) $paid;

        if ($this->amount_paid <= 0) {
            $this->status = 'unpaid';
        } elseif ($this->amount_paid >= $this->amount_billed) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }

        $this->save();
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForStudent($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }
}
