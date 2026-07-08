<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherDetail extends Model
{
    protected $fillable = [
        'user_id', 'birth_place', 'birth_date', 'address',
        'gender', 'religion', 'employment_status',
        'marital_status', 'children_count', 'photo_path',
    ];

    protected $casts = [
        'birth_date'     => 'date',
        'children_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->user->name) . '&background=3B82F6&color=fff';
    }
}
