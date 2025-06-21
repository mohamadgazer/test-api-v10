<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ram extends Model
{
    protected $fillable = ['size_gb', 'price', 'ram_type_id'];



    public function ramType()
    {
        return $this->belongsTo(RamType::class);
    }
    
}
