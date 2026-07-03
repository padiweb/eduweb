<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    // Immutable — tidak ada updated_at
    public $timestamps = false;

    protected $fillable = [
        'school_id', 'user_id', 'user_name', 'user_role',
        'action', 'subject_type', 'subject_id',
        'old_values', 'new_values',
        'ip_address', 'user_agent', 'notes', 'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];
}
