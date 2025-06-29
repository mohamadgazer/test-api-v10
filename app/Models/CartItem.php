<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LaptopDetail;
use App\Models\Ram;
use App\Models\Storage;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity'];

    protected $appends = ['final_price'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function configurations()
    {
        return $this->hasMany(CartItemConfiguration::class);
    }
    

    public function getFinalPriceAttribute()
    {
        if (!$this->product) return 0;

        if (!$this->product->is_composite) {
            return (float) ($this->product->price ?? 0);
        }

        $base = 0;

        $composite = $this->product->composite;

        if ($composite instanceof LaptopDetail) {
            $base += (float) ($composite->base_price ?? 0);

            $ramId = $this->configurations->where('key', 'ram_id')->first()?->value;
            $storageId = $this->configurations->where('key', 'storage_id')->first()?->value;

            $ramPrice = Ram::find($ramId)?->price ?? 0;
            $storagePrice = Storage::find($storageId)?->price ?? 0;

            $base += $ramPrice + $storagePrice;
        }

        return $base;
    }
}
