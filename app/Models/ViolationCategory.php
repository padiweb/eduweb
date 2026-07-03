<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationCategory extends Model
{
    protected $fillable = [
        'school_id', 'name', 'severity', 'default_points',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'category_id');
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'ringan' => 'Ringan',
            'sedang' => 'Sedang',
            'berat'  => 'Berat',
            default  => '-',
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'ringan' => 'blue',
            'sedang' => 'amber',
            'berat'  => 'red',
            default  => 'gray',
        };
    }
}
