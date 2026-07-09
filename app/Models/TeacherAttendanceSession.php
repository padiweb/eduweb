<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherAttendanceSession extends Model
{
    protected $fillable = [
        'school_id', 'session_date', 'session_type',
        'open_time', 'close_time', 'late_after',
        'qr_token', 'is_active',
    ];

    protected $casts = [
        'session_date' => 'date',
        'is_active'    => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'session_id');
    }

    public function isOpen(): bool
    {
        $now = now()->format('H:i:s');
        return $this->is_active
            && $now >= $this->open_time
            && $now <= $this->close_time;
    }

    public function isLate(): bool
    {
        if (! $this->late_after) return false;
        return now()->format('H:i:s') > $this->late_after;
    }

    public function getSessionTypeLabelAttribute(): string
    {
        return $this->session_type === 'masuk' ? 'Masuk' : 'Pulang';
    }
}