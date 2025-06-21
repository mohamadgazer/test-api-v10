<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = ['type', 'size_gb'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
