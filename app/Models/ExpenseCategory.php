<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'school_id', 'name', 'code', 'type',
        'requires_approval', 'approval_threshold', 'is_active',
    ];

    protected $casts = [
        'requires_approval'  => 'boolean',
        'approval_threshold' => 'integer',
        'is_active'          => 'boolean',
    ];

    public function school(): BelongsTo  { return $this->belongsTo(School::class); }
    public function expenses(): HasMany  { return $this->hasMany(Expense::class); }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'payroll'     => 'Penggajian',
            'activity'    => 'Kegiatan',
            'operational' => 'Operasional',
            'other'       => 'Lainnya',
            default       => $this->type,
        };
    }

    // Cek apakah pengeluaran dengan nominal ini butuh approval
    public function needsApproval(int $amount): bool
    {
        if (! $this->requires_approval) return false;
        if ($this->approval_threshold === 0) return true;
        return $amount >= $this->approval_threshold;
    }

    public function scopeForSchool($q, int $id) { return $q->where('school_id', $id); }
    public function scopeActive($q)              { return $q->where('is_active', true); }
}
