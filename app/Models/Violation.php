<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'school_id', 'student_id', 'category_id', 'reported_by',
        'attendance_id', 'incident_date', 'description', 'points',
        'source', 'evidence_path', 'action_taken', 'is_archived',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'is_archived'   => 'boolean',
        'created_at'    => 'datetime',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ViolationCategory::class, 'category_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeManual($query)
    {
        return $query->where('source', 'manual');
    }

    public function scopeAutomatic($query)
    {
        return $query->whereIn('source', [
            'absen_terlambat', 'absen_alfa', 'auto_attendance',
            'tugas_terlambat', 'tugas_tidak_kumpul',
            'prakerin_no_journal', 'prakerin_no_absen',
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'manual'               => 'Manual',
            'absen_terlambat'      => 'Absen Terlambat',
            'absen_alfa'           => 'Alfa',
            'auto_attendance'      => 'Alfa (Otomatis)',
            'tugas_terlambat'      => 'Tugas Terlambat',
            'tugas_tidak_kumpul'   => 'Tidak Kumpul Tugas',
            'prakerin_no_journal'  => 'Tidak Isi Jurnal PKL',
            'prakerin_no_absen'    => 'Tidak Absen PKL',
            default                => $this->source,
        };
    }

    public function isAutomatic(): bool
    {
        return $this->source !== 'manual';
    }
}
