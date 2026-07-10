<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name', 'slug', 'address', 'phone', 'email', 'logo_path', 'npsn',
        'latitude', 'longitude', 'attendance_radius_meters',
        'school_start_time', 'late_threshold_time', 'attendance_close_time',
        'school_program_years',
        'feature_attendance', 'feature_assignments', 'feature_grades',
        'feature_violations', 'feature_journal', 'feature_prakerin',
        'feature_payment_info', 'feature_cbt_integration',
        'package', 'active_until', 'is_active',
        'timezone',
        // Pelanggaran & peringatan
        'violation_warning1',
        'violation_warning2',
        'violation_warning3',
        'alfa_limit_per_semester',
        // Jam absensi guru
        'teacher_checkin_open',
        'teacher_checkin_close',
        'teacher_checkin_late',
        'teacher_checkout_open',
        'teacher_checkout_close',
        'teacher_qr_token',
    ];

    protected $casts = [
        'latitude'                => 'decimal:8',
        'longitude'               => 'decimal:8',
        'active_until'            => 'date',
        'is_active'               => 'boolean',
        'feature_attendance'      => 'boolean',
        'feature_assignments'     => 'boolean',
        'feature_grades'          => 'boolean',
        'feature_violations'      => 'boolean',
        'feature_journal'         => 'boolean',
        'feature_prakerin'        => 'boolean',
        'feature_payment_info'    => 'boolean',
        'feature_cbt_integration' => 'boolean',
        'violation_warning1'      => 'integer',
        'violation_warning2'      => 'integer',
        'violation_warning3'      => 'integer',
        'alfa_limit_per_semester' => 'integer',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function majors(): HasMany
    {
        return $this->hasMany(Major::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function violationCategories(): HasMany
    {
        return $this->hasMany(ViolationCategory::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function activeAcademicYear(): ?AcademicYear
    {
        return $this->academicYears()->where('is_active', true)->first();
    }

    public function hasFeature(string $feature): bool
    {
        $column = 'feature_' . $feature;
        return (bool) ($this->{$column} ?? false);
    }

    public function getTotalSemestersAttribute(): int
    {
        return $this->school_program_years * 2;
    }

    public function isAttendanceOpen(): bool
    {
        $now = now()->format('H:i:s');
        return $now >= $this->school_start_time
            && $now <= $this->attendance_close_time;
    }
}