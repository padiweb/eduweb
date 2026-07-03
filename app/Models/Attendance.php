<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'school_id', 'session_id', 'student_id', 'status',
        'scan_latitude', 'scan_longitude', 'gps_accuracy',
        'distance_from_school', 'is_within_radius', 'scanned_at',
        'ip_address', 'user_agent',
        'permission_reason', 'attachment_path',
        'is_manual_entry', 'entered_by', 'entry_reason', 'entry_at',
        'is_late_scan', 'violation_created',
        'is_flagged', 'flag_reason',
    ];

    protected $casts = [
        'scanned_at'       => 'datetime',
        'entry_at'         => 'datetime',
        'is_within_radius' => 'boolean',
        'is_manual_entry'  => 'boolean',
        'is_late_scan'     => 'boolean',
        'violation_created'=> 'boolean',
        'is_flagged'       => 'boolean',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeHadir($query)
    {
        return $query->whereIn('status', ['hadir', 'terlambat']);
    }

    public function scopeTidakHadir($query)
    {
        return $query->whereIn('status', ['izin', 'sakit', 'alfa']);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('scanned_at', now()->month)
                     ->whereYear('scanned_at', now()->year);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir'     => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            'alfa'      => 'Alfa',
            default     => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'hadir'     => 'emerald',
            'terlambat' => 'amber',
            'izin'      => 'blue',
            'sakit'     => 'purple',
            'alfa'      => 'red',
            default     => 'gray',
        };
    }
}