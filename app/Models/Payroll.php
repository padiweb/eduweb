<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'school_id', 'user_id', 'expense_id', 'academic_year_id',
        'period_label', 'period_date',
        'base_salary', 'jp_count', 'jp_rate', 'jp_total',
        'allowances_total', 'deductions', 'gross_salary', 'net_salary',
        'allowances_detail', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'period_date'      => 'date',
        'base_salary'      => 'integer',
        'jp_count'         => 'integer',
        'jp_rate'          => 'integer',
        'jp_total'         => 'integer',
        'allowances_total' => 'integer',
        'deductions'       => 'integer',
        'gross_salary'     => 'integer',
        'net_salary'       => 'integer',
        'allowances_detail'=> 'array',
    ];

    public function school(): BelongsTo       { return $this->belongsTo(School::class); }
    public function user(): BelongsTo         { return $this->belongsTo(User::class); }
    public function expense(): BelongsTo      { return $this->belongsTo(Expense::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function createdBy(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }

    public function getNetSalaryFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->net_salary, 0, ',', '.');
    }

    public function getGrossSalaryFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->gross_salary, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'approved' => 'Disetujui',
            'paid'     => 'Sudah Dibayar',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'gray',
            'approved' => 'blue',
            'paid'     => 'green',
            default    => 'gray',
        };
    }

    public function scopeForSchool($q, int $id)  { return $q->where('school_id', $id); }
    public function scopePaid($q)                { return $q->where('status', 'paid'); }
    public function scopeForUser($q, int $uid)   { return $q->where('user_id', $uid); }
}
