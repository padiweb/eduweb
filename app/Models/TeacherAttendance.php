<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    protected $fillable = [
        'school_id', 'teacher_id', 'attendance_date', 'status',
        'selfie_path', 'selfie_taken_at',
        'latitude', 'longitude', 'gps_accuracy', 'is_within_radius',
        'check_in_at', 'check_out_at', 'is_late', 'late_minutes',
        'is_verified', 'verified_by', 'verified_at',
        'notes', 'attachment_path', 'ip_address',
    ];

    protected $casts = [
        'attendance_date'  => 'date',
        'selfie_taken_at'  => 'datetime',
        'check_in_at'      => 'datetime',
        'check_out_at'     => 'datetime',
        'verified_at'      => 'datetime',
        'is_within_radius' => 'boolean',
        'is_late'          => 'boolean',
        'is_verified'      => 'boolean',
    ];

    public function teacher(): BelongsTo    { return $this->belongsTo(User::class, 'teacher_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
}