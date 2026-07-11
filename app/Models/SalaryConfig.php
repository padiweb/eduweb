<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryConfig extends Model
{
    protected $fillable = [
        'school_id', 'user_id', 'academic_year_id',
        'base_salary', 'jp_rate', 'is_active', 'notes', 'created_by',
    ];

    protected $casts = [
        'base_salary' => 'integer',
        'jp_rate'     => 'integer',
        'is_active'   => 'boolean',
    ];

    public function school(): BelongsTo       { return $this->belongsTo(School::class); }
    public function user(): BelongsTo         { return $this->belongsTo(User::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function createdBy(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function allowances(): HasMany     { return $this->hasMany(SalaryAllowance::class); }
    public function payrolls(): HasMany       { return $this->hasMany(Payroll::class, 'user_id', 'user_id'); }

    public function getTotalAllowancesAttribute(): int
    {
        return (int) $this->allowances()->sum('amount');
    }

    public function getBaseSalaryFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->base_salary, 0, ',', '.');
    }

    public function getJpRateFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->jp_rate, 0, ',', '.');
    }

    public function scopeForSchool($q, int $id) { return $q->where('school_id', $id); }
    public function scopeActive($q)             { return $q->where('is_active', true); }
}

class SalaryAllowance extends Model
{
    protected $fillable = ['salary_config_id', 'school_id', 'name', 'amount', 'notes'];

    protected $casts = ['amount' => 'integer'];

    public function salaryConfig(): BelongsTo { return $this->belongsTo(SalaryConfig::class); }

    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
