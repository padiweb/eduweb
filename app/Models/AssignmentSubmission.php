<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'assignment_id', 'student_id',
        'content', 'file_path', 'link_url',
        'status', 'score', 'feedback',
        'violation_created',
        'submitted_at', 'graded_at', 'graded_by',
    ];

    protected $casts = [
        'submitted_at'      => 'datetime',
        'graded_at'         => 'datetime',
        'violation_created' => 'boolean',
        'score'             => 'integer',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->score !== null;
    }

    public function getScoreColorAttribute(): string
    {
        if ($this->score === null) return 'gray';
        if ($this->score >= 80) return 'emerald';
        if ($this->score >= 70) return 'blue';
        if ($this->score >= 60) return 'amber';
        return 'red';
    }
}
