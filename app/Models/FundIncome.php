<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundIncome extends Model
{
    protected $fillable = [
        'school_id', 'fund_source_id', 'academic_year_id', 'reference_number',
        'description', 'amount', 'income_date', 'period_label', 'notes',
        'attachment_path', 'created_by',
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount'      => 'integer',
    ];

    public function school(): BelongsTo     { return $this->belongsTo(School::class); }
    public function fundSource(): BelongsTo { return $this->belongsTo(FundSource::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }

    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function scopeForSchool($q, int $id) { return $q->where('school_id', $id); }
}
