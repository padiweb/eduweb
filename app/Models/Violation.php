<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    // Tidak ada updated_at — violation adalah catatan permanen
    public $timestamps = false;

    protected $fillable = [
        'school_id', 'student_id', 'category_id', 'reported_by',
        'incident_date', 'description', 'points', 'source',
        'evidence_path', 'action_taken', 'is_archived',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'is_archived'   => 'boolean',
        'created_at'    => 'datetime',
    ];

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

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'manual'           => 'Manual',
            'auto_attendance'  => 'Otomatis (Absensi)',
            default            => $this->source,
        };
    }
}
