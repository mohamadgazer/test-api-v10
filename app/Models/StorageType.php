<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageType extends Model
{
    protected $fillable = ['name'];

    public function Storage()
    {
        return $this->hasMany(Storage::class);
    }

public function laptopDetails()
{
    return $this->belongsToMany(LaptopDetail::class)->withTimestamps();
}

}
