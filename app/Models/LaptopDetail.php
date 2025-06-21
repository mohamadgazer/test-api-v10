<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaptopDetail extends Model
{
    protected $fillable = [
        'product_id', 'brand_id', 'cpu_id', 'gpu_id',
        'dedicated_gpu_id', 'base_price',
        'default_ram_id', 'default_storage_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
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

    public function defaultRam()
    {
        return $this->belongsTo(Ram::class, 'default_ram_id');
    }

    public function defaultStorage()
    {
        return $this->belongsTo(Storage::class, 'default_storage_id');
    }
}
