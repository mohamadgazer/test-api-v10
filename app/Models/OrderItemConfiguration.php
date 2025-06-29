<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemConfiguration extends Model
{
    protected $fillable = ['order_item_id', 'key', 'value', 'display_name'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
