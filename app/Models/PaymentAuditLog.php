<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAuditLog extends Model
{
    // Audit log TIDAK boleh diupdate atau dihapus
    public $timestamps = false;

    protected $fillable = [
        'school_id', 'user_id', 'action', 'target_type', 'target_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // Override delete — audit log tidak boleh dihapus
    public function delete(): bool
    {
        return false;
    }

    // Override update — audit log tidak boleh diedit
    public function update(array $attributes = [], array $options = []): bool
    {
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Static helper: catat log dari mana saja
    public static function record(
        string $action,
        Model  $target,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): self {
        return static::create([
            'school_id'   => auth()->user()->school_id ?? $target->school_id,
            'user_id'     => auth()->id(),
            'action'      => $action,
            'target_type' => class_basename($target),
            'target_id'   => $target->getKey(),
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'notes'       => $notes,
            'created_at'  => now(),
        ]);
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
