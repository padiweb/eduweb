<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrakerinJournalPhoto extends Model
{
    protected $fillable = ['journal_id', 'photo_path', 'caption', 'sort_order'];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(PrakerinJournal::class, 'journal_id');
    }
}
