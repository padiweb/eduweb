<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDiscount extends Model
{
    protected $fillable = [
        'school_id', 'user_id', 'payment_type_id', 'academic_year_id',
        'name', 'discount_type', 'discount_value', 'scholarship_type',
        'valid_from', 'valid_until', 'notes', 'created_by',
        'discount_program_id',
    ];

    protected $casts = [
        'valid_from'     => 'date',
        'valid_until'    => 'date',
        'discount_value' => 'integer',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Hitung potongan dalam rupiah dari nominal tagihan
    public function calculateDiscount(int $baseAmount): int
    {
        if ($this->discount_type === 'percent') {
            // Pastikan persen tidak melebihi 100
            $pct = min((int) $this->discount_value, 100);
            return (int) round($baseAmount * $pct / 100);
        }
        // Fixed: tidak boleh melebihi nominal tagihan
        return min((int) $this->discount_value, $baseAmount);
    }

    // Apakah masih aktif hari ini
    public function isActiveNow(): bool
    {
        $today = now()->toDateString();
        if ($this->valid_from > $today) return false;
        if ($this->valid_until && $this->valid_until < $today) return false;
        return true;
    }

    public function getDiscountLabel(): string
    {
        if ($this->discount_type === 'percent') {
            return $this->discount_value . '%';
        }
        return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActiveNow($query)
    {
        $today = now()->toDateString();
        return $query->where('valid_from', '<=', $today)
                     ->where(function ($q) use ($today) {
                         $q->whereNull('valid_until')
                           ->orWhere('valid_until', '>=', $today);
                     });
    }
}
