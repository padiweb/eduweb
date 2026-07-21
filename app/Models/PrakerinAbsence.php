<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrakerinAbsence extends Model
{
    protected $fillable = [
        'placement_id', 'student_id', 'absence_date', 'type',
        'reason', 'attachment_path',
        'status', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'absence_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    public function placement(): BelongsTo  { return $this->belongsTo(PrakerinPlacement::class); }
    public function student(): BelongsTo    { return $this->belongsTo(User::class, 'student_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            'libur' => 'Libur DU/DI',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }

    public function isApproved(): bool { return $this->status === 'approved'; }
}
