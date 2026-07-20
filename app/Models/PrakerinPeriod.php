<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrakerinPeriod extends Model
{
    protected $fillable = [
        'school_id', 'academic_year_id', 'name',
        'start_date', 'end_date', 'is_active', 'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function school(): BelongsTo       { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo  { return $this->belongsTo(AcademicYear::class); }
    public function locations(): HasMany       { return $this->hasMany(PrakerinLocation::class, 'period_id'); }
    public function placements(): HasMany      { return $this->hasMany(PrakerinPlacement::class, 'period_id'); }

    public function isOngoing(): bool
    {
        return $this->is_active && today()->between($this->start_date, $this->end_date);
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) return 'Nonaktif';
        if (today()->lt($this->start_date)) return 'Belum Mulai';
        if (today()->gt($this->end_date)) return 'Selesai';
        return 'Berlangsung';
    }
}
