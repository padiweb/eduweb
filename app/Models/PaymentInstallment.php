<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentInstallment extends Model
{
    protected $fillable = [
        'payment_bill_id', 'school_id', 'installment_number',
        'amount_due', 'amount_paid', 'due_date', 'status',
    ];

    protected $casts = [
        'due_date'           => 'date',
        'amount_due'         => 'integer',
        'amount_paid'        => 'integer',
        'installment_number' => 'integer',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(PaymentBill::class, 'payment_bill_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function getAmountRemainingAttribute(): int
    {
        return max(0, $this->amount_due - $this->amount_paid);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }
}
