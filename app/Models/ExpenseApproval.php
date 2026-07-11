<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseApproval extends Model {
    protected $fillable = ['expense_id','school_id','user_id','action','notes','ip_address'];
    public function expense(): BelongsTo { return $this->belongsTo(Expense::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function getActionLabelAttribute(): string {
        return match($this->action) {
            'submitted' => 'Diajukan',
            'approved'  => 'Disetujui',
            'rejected'  => 'Ditolak',
            'revised'   => 'Dikembalikan',
            default     => $this->action,
        };
    }
}
