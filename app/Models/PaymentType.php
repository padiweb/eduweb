<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentType extends Model
{
    protected $fillable = [
        'school_id', 'name', 'code', 'category',
        'period_type', 'is_active', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(PaymentRate::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(PaymentBill::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(StudentDiscount::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'spp'      => 'SPP',
            'ujian'    => 'Ujian',
            'kegiatan' => 'Kegiatan',
            'seragam'  => 'Seragam/Buku',
            'lainnya'  => 'Lainnya',
            default    => $this->category,
        };
    }

    public function getPeriodTypeLabelAttribute(): string
    {
        return match($this->period_type) {
            'monthly'  => 'Bulanan',
            'semester' => 'Per semester',
            'once'     => 'Sekali bayar',
            default    => $this->period_type,
        };
    }

    // Scope: hanya tampilkan yang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: filter per sekolah (multi-tenant safety)
    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
