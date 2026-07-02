<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceSession extends Model
{
    protected $fillable = [
        'school_id', 'classroom_id', 'subject_id', 'teacher_id', 'schedule_id',
        'session_date', 'qr_token', 'qr_token_hash', 'token_expires_at',
        'school_latitude', 'school_longitude', 'radius_meters',
        'is_closed', 'closed_at', 'roll_call_done', 'roll_call_at',
    ];

    protected $casts = [
        'session_date'     => 'date',
        'token_expires_at' => 'datetime',
        'closed_at'        => 'datetime',
        'roll_call_at'     => 'datetime',
        'is_closed'        => 'boolean',
        'roll_call_done'   => 'boolean',
    ];

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function subject(): BelongsTo   { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo   { return $this->belongsTo(User::class, 'teacher_id'); }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_closed', false)
                     ->where('token_expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->token_expires_at);
    }

    public function isActive(): bool
    {
        return ! $this->is_closed && ! $this->isExpired();
    }

    public function verifyToken(string $plainToken): bool
    {
        return hash_equals($this->qr_token_hash, hash('sha256', $plainToken));
    }

    public function getMissingStudentsAttribute()
    {
        $presentIds = $this->attendances()->pluck('student_id');
        return $this->classroom->students()->whereNotIn('users.id', $presentIds)->get();
    }

    public function close(): void
    {
        $this->update(['is_closed' => true, 'closed_at' => now()]);
    }
}