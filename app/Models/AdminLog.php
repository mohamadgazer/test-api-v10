<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_id', 'action', 'target_model', 'target_id', 'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
