<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'school_id', 'payment_bill_id', 'payment_installment_id', 'user_id',
        'reference_number', 'amount', 'channel', 'status',
        'receipt_path', 'bank_name', 'sender_name', 'transfer_date', 'notes',
        'cashier_notes', 'confirmed_by', 'confirmed_at',
        'rejection_reason', 'cancellation_reason',
        'created_by', 'created_by_ip',
    ];

    protected $casts = [
        'amount'        => 'integer',
        'transfer_date' => 'date',
        'confirmed_at'  => 'datetime',
    ];

    // Model ini TIDAK boleh di-update kecuali status + kolom konfirmasi
    // Enforced via PaymentTransactionPolicy

    public function bill(): BelongsTo
    {
        return $this->belongsTo(PaymentBill::class, 'payment_bill_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'payment_installment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'Menunggu konfirmasi',
            'approved'  => 'Dikonfirmasi',
            'rejected'  => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'yellow',
            'approved'  => 'green',
            'rejected'  => 'red',
            'cancelled' => 'gray',
            default     => 'gray',
        };
    }

    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            'cash'     => 'Tunai',
            'transfer' => 'Transfer bank',
            default    => $this->channel,
        };
    }

    // Generate reference number unik
    public static function generateReference(int $schoolId): string
    {
        $prefix = 'PAY';
        $date   = now()->format('ymd');
        $rand   = strtoupper(substr(md5(uniqid('', true)), 0, 6));
        return "{$prefix}-{$date}-{$rand}";
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
