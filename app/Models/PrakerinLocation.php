<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrakerinLocation extends Model
{
    protected $fillable = [
        'school_id', 'period_id', 'name', 'address',
        'latitude', 'longitude', 'radius_meters',
        'field_supervisor_name', 'field_supervisor_phone',
        'checkin_time', 'checkout_time', 'checkin_late_after',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo   { return $this->belongsTo(School::class); }
    public function period(): BelongsTo   { return $this->belongsTo(PrakerinPeriod::class, 'period_id'); }
    public function placements(): HasMany { return $this->hasMany(PrakerinPlacement::class, 'location_id'); }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'prakerin_loc_supervisors', 'location_id', 'teacher_id')
                    ->withTimestamps();
    }

    /** Jumlah siswa aktif di lokasi ini */
    public function getActiveStudentCountAttribute(): int
    {
        return $this->placements()->where('is_active', true)->count();
    }
}
