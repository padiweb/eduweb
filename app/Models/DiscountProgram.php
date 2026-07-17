<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountProgram extends Model
{
    protected $fillable = [
        'school_id', 'academic_year_id', 'payment_type_id',
        'name', 'code', 'discount_type', 'default_value',
        'valid_from', 'valid_until', 'description', 'is_active', 'created_by',
    ];

    protected $casts = [
        'default_value' => 'integer',
        'is_active'     => 'boolean',
        'valid_from'    => 'date',
        'valid_until'   => 'date',
    ];

    public function school(): BelongsTo       { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo  { return $this->belongsTo(AcademicYear::class); }
    public function paymentType(): BelongsTo   { return $this->belongsTo(PaymentType::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, 'created_by'); }
    public function members(): HasMany         { return $this->hasMany(DiscountProgramMember::class); }

    public function getDefaultValueFormattedAttribute(): string
    {
        if ($this->discount_type === 'percent') {
            return $this->default_value . '%';
        }
        return 'Rp ' . number_format($this->default_value, 0, ',', '.');
    }

    public function getMembersCountAttribute(): int
    {
        return $this->members()->count();
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
            ->where('valid_from', '<=', now()->toDateString())
            ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString()));
    }
}
