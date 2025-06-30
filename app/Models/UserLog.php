<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'target_model', 'target_id', 'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
