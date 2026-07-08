<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    protected $fillable = ['school_id', 'name'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_positions', 'position_id', 'teacher_id')
                    ->withTimestamps();
    }
}
