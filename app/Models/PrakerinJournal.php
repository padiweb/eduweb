<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrakerinJournal extends Model
{
    protected $fillable = [
        'placement_id', 'student_id', 'journal_date', 'content',
        'status', 'submitted_at',
        'teacher_note', 'noted_by', 'noted_at',
        'violation_created',
    ];

    protected $casts = [
        'journal_date'      => 'date',
        'submitted_at'      => 'datetime',
        'noted_at'          => 'datetime',
        'violation_created' => 'boolean',
    ];

    public function placement(): BelongsTo { return $this->belongsTo(PrakerinPlacement::class); }
    public function student(): BelongsTo   { return $this->belongsTo(User::class, 'student_id'); }
    public function notedBy(): BelongsTo   { return $this->belongsTo(User::class, 'noted_by'); }
    public function photos(): HasMany      { return $this->hasMany(PrakerinJournalPhoto::class, 'journal_id')->orderBy('sort_order'); }
}
