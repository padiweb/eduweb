<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    protected $fillable = [
        'school_id', 'session_id', 'teacher_id', 'status',
        'latitude', 'longitude', 'distance_meters', 'is_within_radius',
        'notes', 'attachment_path', 'is_manual_entry', 'scanned_at',
        // kolom lama yang masih ada di tabel
        'attendance_date', 'selfie_path', 'selfie_taken_at',
        'gps_accuracy', 'check_in_at', 'check_out_at',
        'is_late', 'late_minutes', 'is_verified', 'verified_by', 'verified_at',
        'ip_address',
    ];

    protected $casts = [
        'attendance_date'  => 'date',
        'scanned_at'       => 'datetime',
        'selfie_taken_at'  => 'datetime',
        'check_in_at'      => 'datetime',
        'check_out_at'     => 'datetime',
        'verified_at'      => 'datetime',
        'is_within_radius' => 'boolean',
        'is_manual_entry'  => 'boolean',
        'is_late'          => 'boolean',
        'is_verified'      => 'boolean',
        'distance_meters'  => 'float',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TeacherAttendanceSession::class, 'session_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'hadir'     => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            'dinas'     => 'Perjalanan Dinas',
            'alfa'      => 'Alfa',
            default     => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'hadir'     => 'emerald',
            'terlambat' => 'amber',
            'izin'      => 'blue',
            'sakit'     => 'purple',
            'dinas'     => 'cyan',
            'alfa'      => 'red',
            default     => 'gray',
        };
    }
}