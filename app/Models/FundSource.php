<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundSource extends Model
{
    protected $fillable = [
        'school_id', 'name', 'code', 'type', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(FundIncome::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // Total pemasukan dari sumber ini
    public function getTotalIncomeAttribute(): int
    {
        return (int) $this->incomes()->sum('amount');
    }

    // Total pengeluaran dari sumber ini
    public function getTotalExpenseAttribute(): int
    {
        return (int) $this->expenses()->whereIn('status', ['approved'])->sum('amount');
    }

    // Saldo sumber ini
    public function getBalanceAttribute(): int
    {
        return $this->total_income - $this->total_expense;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'siswa'  => 'Kas Siswa',
            'bos'    => 'Dana BOS',
            'bosda'  => 'Dana BOSDA',
            'other'  => 'Lainnya',
            default  => $this->type,
        };
    }

    public function getTypeBadgeColor(): string
    {
        return match($this->type) {
            'siswa'  => 'teal',
            'bos'    => 'blue',
            'bosda'  => 'purple',
            'other'  => 'gray',
            default  => 'gray',
        };
    }

    // Scope
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeForSchool($q, int $schoolId) { return $q->where('school_id', $schoolId); }
}
