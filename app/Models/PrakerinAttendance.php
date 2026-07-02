<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrakerinAttendance extends Model
{
    protected $fillable = [
        'placement_id', 'student_id', 'attendance_date', 'type', 'status',
        'selfie_path', 'selfie_taken_at',
        'latitude', 'longitude', 'gps_accuracy',
        'distance_from_company', 'is_within_geofence',
        'ip_address', 'user_agent',
        'is_verified', 'verified_by', 'verified_at', 'notes',
    ];

    protected $casts = [
        'attendance_date'   => 'date',
        'selfie_taken_at'   => 'datetime',
        'verified_at'       => 'datetime',
        'is_within_geofence'=> 'boolean',
        'is_verified'       => 'boolean',
    ];

    public function placement(): BelongsTo  { return $this->belongsTo(PrakerinPlacement::class); }
    public function student(): BelongsTo    { return $this->belongsTo(User::class, 'student_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
}