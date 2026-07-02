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
        'ip_address', 'user_agent', 'permission_reason', 'attachment_path',
        'is_manual_override', 'override_by', 'override_reason', 'override_at',
        'is_flagged', 'flag_reason',
    ];

    protected $casts = [
        'scanned_at'         => 'datetime',
        'override_at'        => 'datetime',
        'is_within_radius'   => 'boolean',
        'is_manual_override' => 'boolean',
        'is_flagged'         => 'boolean',
    ];

    public function session(): BelongsTo   { return $this->belongsTo(AttendanceSession::class, 'session_id'); }
    public function student(): BelongsTo   { return $this->belongsTo(User::class, 'student_id'); }
    public function overrideBy(): BelongsTo { return $this->belongsTo(User::class, 'override_by'); }

    public function scopeHadir($query)      { return $query->whereIn('status', ['hadir', 'terlambat']); }
    public function scopeTidakHadir($query) { return $query->whereIn('status', ['izin', 'sakit', 'alfa']); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
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
        return match($this->status) {
            'hadir'     => 'green',
            'terlambat' => 'amber',
            'izin'      => 'blue',
            'sakit'     => 'purple',
            'alfa'      => 'red',
            default     => 'gray',
        };
    }
}