<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasSetoran extends Model
{
    protected $fillable = [
        'school_id', 'academic_year_id', 'fund_source_id',
        'tanggal_setoran', 'total_tunai', 'total_transfer',
        'total_setoran', 'keterangan', 'no_referensi',
        'status', 'created_by', 'disetor_at',
    ];

    protected $casts = [
        'tanggal_setoran' => 'date',
        'disetor_at'      => 'datetime',
        'total_tunai'     => 'integer',
        'total_transfer'  => 'integer',
        'total_setoran'   => 'integer',
    ];

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function fundSource(): BelongsTo   { return $this->belongsTo(FundSource::class); }
    public function createdBy(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }

    public function getTotalSetoranFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_setoran, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'setor' ? 'Sudah Disetor' : 'Draft';
    }
}
