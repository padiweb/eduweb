<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountProgramMember extends Model
{
    protected $fillable = [
        'discount_program_id', 'user_id', 'override_value', 'notes', 'created_by',
    ];

    protected $casts = [
        'override_value' => 'integer',
    ];

    public function program(): BelongsTo  { return $this->belongsTo(DiscountProgram::class, 'discount_program_id'); }
    public function student(): BelongsTo  { return $this->belongsTo(User::class, 'user_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    // Nilai efektif: pakai override jika ada, kalau tidak pakai default program
    public function getEffectiveValueAttribute(): int
    {
        return $this->override_value ?? $this->program->default_value;
    }

    public function getEffectiveValueFormattedAttribute(): string
    {
        $val = $this->effective_value;
        if ($this->program->discount_type === 'percent') {
            return $val . '%';
        }
        return 'Rp ' . number_format($val, 0, ',', '.');
    }
}
