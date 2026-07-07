<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'school_id', 'name', 'email', 'username', 'password',
        'nis', 'nip', 'role', 'avatar_path',
        'last_login_at', 'last_login_ip',
        'failed_attempts', 'locked_until', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'locked_until'      => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_students', 'student_id', 'classroom_id')
                    ->withPivot('student_number')
                    ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function teacherAttendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'teacher_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'teacher_id');
    }

    // Relasi pelanggaran — dibutuhkan oleh ViolationController::index()
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'student_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isSiswa(): bool      { return $this->role === 'siswa'; }
    public function isGuru(): bool       { return $this->role === 'guru'; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isKesiswaan(): bool  { return $this->role === 'kesiswaan'; }
    public function isWaliKelas(): bool  { return $this->role === 'wali_kelas'; }

    public function canManageAttendance(): bool
    {
        return in_array($this->role, ['guru', 'wali_kelas', 'kesiswaan', 'admin']);
    }

    public function isLocked(): bool
    {
        return $this->locked_until && now()->isBefore($this->locked_until);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        return strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1D9E75&color=fff';
    }
}