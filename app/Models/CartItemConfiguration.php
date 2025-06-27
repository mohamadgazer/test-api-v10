<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemConfiguration extends Model
{
    protected $fillable = ['cart_item_id', 'key', 'value'];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }
}
