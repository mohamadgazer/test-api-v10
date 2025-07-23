<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
   protected $fillable = ['size', 'price', 'storage_type_id'];


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function storageType()
{
    return $this->belongsTo(StorageType::class);
}
}
