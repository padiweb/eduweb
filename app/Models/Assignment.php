<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'school_id', 'classroom_id', 'subject_id', 'teacher_id',
        'title', 'description', 'attachment_path',
        'submission_type', 'deadline', 'is_closed', 'closed_at', 'max_score',
    ];

    protected $casts = [
        'deadline'  => 'datetime',
        'closed_at' => 'datetime',
        'is_closed' => 'boolean',
        'max_score' => 'integer',
    ];

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function subject(): BelongsTo   { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo   { return $this->belongsTo(User::class, 'teacher_id'); }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function scopeOpen($query)        { return $query->where('is_closed', false); }
    public function isOpen(): bool           { return ! $this->is_closed; }
    public function isPastDeadline(): bool   { return $this->deadline && now()->isAfter($this->deadline); }

    public function getSubmissionTypeLabel(): string
    {
        return match ($this->submission_type) {
            'file' => 'File',
            'text' => 'Teks',
            'link' => 'Link',
            'any'  => 'File / Teks / Link',
            default => $this->submission_type,
        };
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? asset('storage/' . $this->attachment_path)
            : null;
    }
}