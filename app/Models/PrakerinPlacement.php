<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrakerinPlacement extends Model
{
    protected $fillable = [
        'school_id', 'period_id', 'location_id', 'student_id',
        'start_date', 'end_date', 'is_active', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function period(): BelongsTo    { return $this->belongsTo(PrakerinPeriod::class, 'period_id'); }
    public function location(): BelongsTo  { return $this->belongsTo(PrakerinLocation::class, 'location_id'); }
    public function student(): BelongsTo   { return $this->belongsTo(User::class, 'student_id'); }
    public function attendances(): HasMany { return $this->hasMany(PrakerinAttendance::class, 'placement_id'); }
    public function journals(): HasMany    { return $this->hasMany(PrakerinJournal::class, 'placement_id'); }

    public function getEffectiveStartDate()
    {
        return $this->start_date ?? $this->period->start_date;
    }

    public function getEffectiveEndDate()
    {
        return $this->end_date ?? $this->period->end_date;
    }

    public function isActiveToday(): bool
    {
        $today = today();
        return $this->is_active
            && $today->between($this->getEffectiveStartDate(), $this->getEffectiveEndDate());
    }
}
