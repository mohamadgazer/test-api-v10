<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'stock', 'image',
        'category_id', 'brand_id', 'product_model_id'
    ];

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
        return $this->hasOne(LaptopDetail::class);
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
