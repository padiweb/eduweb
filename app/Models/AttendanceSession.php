<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceSession extends Model
{
    protected $fillable = [
        'school_id', 'classroom_id', 'opened_by', 'session_date',
        'qr_token_hash', 'qr_generated_at',
        'open_time', 'close_time', 'late_after',
        'school_latitude', 'school_longitude', 'radius_meters',
        'is_closed', 'closed_at',
        'roll_call_done', 'roll_call_by', 'roll_call_at',
    ];

    protected $casts = [
        'session_date'    => 'date',
        'qr_generated_at' => 'datetime',
        'closed_at'       => 'datetime',
        'roll_call_at'    => 'datetime',
        'is_closed'       => 'boolean',
        'roll_call_done'  => 'boolean',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function rollCallBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'roll_call_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(AttendanceValidation::class, 'session_id');
    }

    // ── Scope ──────────────────────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('session_date', today());
    }

    public function scopeActive($query)
    {
        return $query->where('is_closed', false);
    }

    // ── Status helpers ─────────────────────────────────────────────────────

    /**
     * Apakah sekarang dalam jam aktif scan siswa?
     */
    public function isWithinScanTime(): bool
    {
        $now = now()->format('H:i:s');
        return $now >= $this->open_time && $now <= $this->close_time;
    }

    /**
     * Apakah scan saat ini akan tercatat terlambat?
     */
    public function isLateNow(): bool
    {
        return now()->format('H:i:s') > $this->late_after;
    }

    /**
     * Apakah scan sudah lewat jam tutup (scan di luar jam)?
     */
    public function isAfterClose(): bool
    {
        return now()->format('H:i:s') > $this->close_time;
    }

    public function isActive(): bool
    {
        return ! $this->is_closed;
    }

    // ── Data helpers ───────────────────────────────────────────────────────

    /**
     * Daftar siswa yang BELUM absen di sesi ini.
     */
    public function getMissingStudentsAttribute()
    {
        $presentIds = $this->attendances()->pluck('student_id');
        return $this->classroom->students()
                               ->whereNotIn('users.id', $presentIds)
                               ->orderBy('users.name')
                               ->get();
    }

    /**
     * Verifikasi plain token dari QR scan.
     */
    public function verifyToken(string $plainToken): bool
    {
        return hash_equals($this->qr_token_hash, hash('sha256', $plainToken));
    }

    public function close(): void
    {
        $this->update(['is_closed' => true, 'closed_at' => now()]);
    }
}