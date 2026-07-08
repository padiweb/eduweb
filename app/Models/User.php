<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'school_id', 'name', 'email', 'username', 'password',
        'nis', 'nisn', 'nip', 'niy', 'phone', 'role',
        'avatar_path', 'last_login_at', 'last_login_ip',
        'failed_attempts', 'locked_until', 'is_active',
        'student_status', 'status_changed_at', 'status_notes',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'last_login_at'      => 'datetime',
        'locked_until'       => 'datetime',
        'status_changed_at'  => 'date',
        'is_active'          => 'boolean',
        'password'           => 'hashed',
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

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'student_id');
    }

    public function studentDetail(): HasOne
    {
        return $this->hasOne(StudentDetail::class);
    }

    public function teacherDetail(): HasOne
    {
        return $this->hasOne(TeacherDetail::class);
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'teacher_positions', 'teacher_id', 'position_id')
                    ->withTimestamps();
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isSiswa(): bool     { return $this->role === 'siswa'; }
    public function isGuru(): bool      { return in_array($this->role, ['guru', 'wali_kelas']); }
    public function isAdmin(): bool     { return $this->role === 'admin'; }
    public function isKesiswaan(): bool { return $this->role === 'kesiswaan'; }
    public function isAlumni(): bool    { return $this->student_status === 'alumni'; }
    public function isAktif(): bool     { return $this->student_status === 'aktif'; }

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
        return strtoupper(
            substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : '')
        );
    }

    public function getAvatarUrlAttribute(): string
    {
        $photo = $this->studentDetail?->photo_path ?? $this->teacherDetail?->photo_path;
        if ($photo) return asset('storage/' . $photo);
        if ($this->avatar_path) return asset('storage/' . $this->avatar_path);

        $bg = match($this->role) {
            'siswa'     => '1D9E75',
            'guru'      => '3B82F6',
            'admin'     => '8B5CF6',
            'kesiswaan' => 'F59E0B',
            default     => '6B7280',
        };
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=' . $bg . '&color=fff';
    }

    public function getStudentStatusLabelAttribute(): string
    {
        return match($this->student_status ?? 'aktif') {
            'aktif'  => 'Aktif',
            'alumni' => 'Alumni',
            'keluar' => 'Keluar',
            'pindah' => 'Pindah',
            default  => 'Aktif',
        };
    }
}
