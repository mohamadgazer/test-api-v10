<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 
        'price', 
        'stock', 'image',
        'category_id', 'brand_id', 'product_model_id'
    ];
    protected $appends = ['final_price'];

 
    public function getFinalPriceAttribute()
    {
        if ($this->laptopDetails) {
            return
                ($this->laptopDetails->base_price ?? 0) +
                ($this->laptopDetails->defaultRam->price ?? 0) +
                ($this->laptopDetails->defaultStorage->price ?? 0);
        }
    
        return (float) ($this->price ?? 0);
    }
    
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function laptopDetails()
    {
        return $this->hasOne(LaptopDetail::class, 'product_model_id', 'product_model_id');
    }
    
    public function rams()
{
    return $this->belongsToMany(Ram::class, 'laptop_rams');
}

public function storages()
{
    return $this->belongsToMany(Storage::class, 'laptop_storages');
}


}
