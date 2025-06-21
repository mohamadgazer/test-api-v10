<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaptopDetail extends Model
{
    protected $fillable = [
        'product_model_id',
        'brand_id',
        'cpu_id',
        'gpu_id',
        'dedicated_gpu_id',
        'base_price',
        'default_ram_id',
        'default_storage_id'
    ];


    public function productModel()
{
    return $this->belongsTo(ProductModel::class);
}

    public function rams()
    {
        return $this->belongsToMany(Ram::class, 'laptop_rams', 'laptop_id', 'ram_id');
    }
    
    public function storages()
    {
        return $this->belongsToMany(Storage::class, 'laptop_storages', 'laptop_id', 'storage_id');
    }
    
    public function defaultRam()
    {
        return $this->belongsTo(Ram::class, 'default_ram_id');
    }
    
    public function defaultStorage()
    {
        return $this->belongsTo(Storage::class, 'default_storage_id');
    }

    public function cpu()
{
    return $this->belongsTo(CPU::class);
}

public function gpu()
{
    return $this->belongsTo(GPU::class);
}

public function dedicatedGpu()
{
    return $this->belongsTo(DedicatedGPU::class);
}

public function brand()
{
    return $this->belongsTo(Brand::class);
}

    
}
