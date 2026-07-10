<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingJournal extends Model
{
    protected $fillable = [
        'school_id', 'teacher_id', 'schedule_id', 'classroom_id', 'subject_id',
        'journal_date', 'meeting_number', 'topic', 'description',
        'method', 'students_present', 'students_absent',
        'photo_path', 'notes', 'is_reward_given',
    ];

    protected $casts = [
        'journal_date'    => 'date',
        'is_reward_given' => 'boolean',
    ];

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function teacher(): BelongsTo   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function schedule(): BelongsTo  { return $this->belongsTo(Schedule::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function subject(): BelongsTo   { return $this->belongsTo(Subject::class); }

    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'ceramah'      => 'Ceramah',
            'diskusi'      => 'Diskusi',
            'praktek'      => 'Praktik',
            'demonstrasi'  => 'Demonstrasi',
            'presentasi'   => 'Presentasi',
            'tanya_jawab'  => 'Tanya Jawab',
            default        => 'Lainnya',
        };
    }
}
