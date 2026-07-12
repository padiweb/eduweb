<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRateOverride extends Model
{
    protected $fillable = [
        'school_id', 'user_id', 'payment_type_id', 'academic_year_id',
        'amount', 'reason', 'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function student(): BelongsTo     { return $this->belongsTo(User::class, 'user_id'); }
    public function paymentType(): BelongsTo { return $this->belongsTo(PaymentType::class); }
    public function academicYear(): BelongsTo{ return $this->belongsTo(AcademicYear::class); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }

    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function scopeForSchool($q, int $id) { return $q->where('school_id', $id); }
}
