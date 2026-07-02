<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrakerinPlacement extends Model
{
    protected $fillable = [
        'school_id', 'student_id', 'academic_year_id', 'supervisor_teacher_id',
        'company_name', 'company_address', 'latitude', 'longitude', 'radius_meters',
        'field_supervisor_name', 'field_supervisor_phone',
        'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function student(): BelongsTo          { return $this->belongsTo(User::class, 'student_id'); }
    public function supervisorTeacher(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_teacher_id'); }
    public function academicYear(): BelongsTo     { return $this->belongsTo(AcademicYear::class); }

    public function attendances(): HasMany
    {
        return $this->hasMany(PrakerinAttendance::class, 'placement_id');
    }
}