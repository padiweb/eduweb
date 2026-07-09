<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherRewardPoint extends Model
{
    protected $fillable = [
        'school_id', 'teacher_id', 'type', 'points',
        'description', 'point_date', 'reference_id', 'reference_type',
    ];

    protected $casts = [
        'point_date' => 'date',
        'points'     => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'absen_tepat_waktu' => 'Absen Tepat Waktu',
            'isi_jurnal'        => 'Isi Jurnal Mengajar',
            'bonus'             => 'Bonus',
            'pengurang'         => 'Pengurang',
            default             => $this->type,
        };
    }
}
