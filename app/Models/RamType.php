<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RamType extends Model
{
    protected $fillable = ['name'];

    public function rams()
    {
        return $this->hasMany(Ram::class);
    }
}
