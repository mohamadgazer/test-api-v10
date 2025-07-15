<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
protected $fillable = [
    'name', 
    'description', 
    'price', 
    'stock', 
    'category_id', 
    'brand_id',
    'is_composite',
    'composite_type',
    'composite_id'
];


    protected $casts = [
        'is_composite' => 'boolean',
    ];

    protected $appends = ['final_price'];

    // ========== العلاقات ==========

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * العلاقة المركبة polymorphic (مثل LaptopDetail أو أي نوع آخر).
     */
    public function composite()
    {
        return $this->morphTo();
    }


public function mainImage()
{
    return $this->hasOne(ProductImage::class)->where('is_main', true);
}

    

    // ========== السعر النهائي ==========

    public function getFinalPriceAttribute()
    {
        if ($this->is_composite && $this->composite) {
            return 
                ($this->composite->base_price ?? 0) +
                ($this->composite->defaultRam->price ?? 0) +
                ($this->composite->defaultStorage->price ?? 0);
        }

        return (float) ($this->price ?? 0);
    }
}
