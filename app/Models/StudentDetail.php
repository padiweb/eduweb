<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StudentDetail extends Model
{
    protected $fillable = [
        'user_id', 'birth_place', 'birth_date', 'address',
        'province', 'regency', 'district', 'village', 'street',
        'is_abroad', 'country',
        'gender', 'religion', 'nik', 'no_kk', 'whatsapp',
        'father_name', 'mother_name', 'parent_whatsapp', 'photo_path',
    ];

    protected $casts = [
        'birth_date' => 'date',
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
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->user->name) . '&background=1D9E75&color=fff';
    }
}
