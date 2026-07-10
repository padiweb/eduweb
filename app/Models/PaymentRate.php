<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentRate extends Model
{
    protected $fillable = [
        'payment_type_id', 'school_id', 'academic_year_id',
        'classroom_id', 'major_id', 'amount', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount'    => 'integer',
    ];

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(PaymentBill::class);
    }

    // Format rupiah (helper)
    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    // Label "berlaku untuk" (semua / kelas X / jurusan Y)
    public function getScopeLabel(): string
    {
        if ($this->classroom_id && $this->major_id) {
            return ($this->classroom->name ?? '?') . ' - ' . ($this->major->name ?? '?');
        }
        if ($this->classroom_id) {
            return $this->classroom->name ?? '?';
        }
        if ($this->major_id) {
            return $this->major->name ?? '?';
        }
        return 'Semua kelas';
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
