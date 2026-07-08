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

    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function student(): BelongsTo    { return $this->belongsTo(User::class, 'student_id'); }
    public function gradedBy(): BelongsTo   { return $this->belongsTo(User::class, 'graded_by'); }

    public function isLate(): bool          { return $this->status === 'late'; }
    public function isGraded(): bool        { return $this->status === 'graded' && $this->score !== null; }
    public function isNotSubmitted(): bool  { return $this->status === 'not_submitted'; }
    public function hasContent(): bool      { return (bool)($this->file_path || $this->link_url || $this->content); }
}