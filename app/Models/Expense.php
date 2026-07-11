<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'fund_source_id', 'expense_category_id', 'academic_year_id',
        'reference_number', 'description', 'amount', 'expense_date', 'period_label',
        'notes', 'attachment_path', 'status', 'approved_by', 'approved_at',
        'rejection_reason', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
        'amount'       => 'integer',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    public function school(): BelongsTo        { return $this->belongsTo(School::class); }
    public function fundSource(): BelongsTo    { return $this->belongsTo(FundSource::class); }
    public function category(): BelongsTo      { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }
    public function academicYear(): BelongsTo  { return $this->belongsTo(AcademicYear::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy(): BelongsTo    { return $this->belongsTo(User::class, 'approved_by'); }
    public function approvals(): HasMany       { return $this->hasMany(ExpenseApproval::class); }
    public function payroll(): ?Payroll        { return $this->hasOne(Payroll::class)->first(); }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'            => 'Draft',
            'pending_approval' => 'Menunggu Approval',
            'approved'         => 'Disetujui',
            'rejected'         => 'Ditolak',
            'cancelled'        => 'Dibatalkan',
            default            => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'            => 'gray',
            'pending_approval' => 'amber',
            'approved'         => 'green',
            'rejected'         => 'red',
            'cancelled'        => 'gray',
            default            => 'gray',
        };
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForSchool($q, int $id)    { return $q->where('school_id', $id); }
    public function scopePending($q)               { return $q->where('status', 'pending_approval'); }
    public function scopeApproved($q)              { return $q->where('status', 'approved'); }
    public function scopeBySource($q, int $id)     { return $q->where('fund_source_id', $id); }
    public function scopeByCategory($q, int $id)   { return $q->where('expense_category_id', $id); }
}
