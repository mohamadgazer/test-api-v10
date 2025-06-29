<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
        ]);

        $userId = Auth::id();
        $cartItems = CartItem::with(['product', 'configurations'])->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            $total = 0;

            foreach ($cartItems as $item) {
                if ($item->quantity > $item->product->stock) {
                    throw new \Exception("Product {$item->product->name} does not have enough stock.");
                }

                $total += $item->final_price * $item->quantity;
            }

            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'address' => $request->address,
                'status' => 'processing',
            ]);

            foreach ($cartItems as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->final_price,
                ]);

                // نسخ التكوينات من CartItemConfiguration إلى OrderItemConfiguration
                foreach ($item->configurations as $config) {
                    $orderItem->configurations()->create([
                        'key' => $config->key,
                        'value' => $config->value,
                        'display_name' => $config->display_name,
                    ]);
                }

                $item->product->decrement('stock', $item->quantity);
            }

            CartItem::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Checkout completed',
                'order_id' => $order->id,
                'total' => $order->total,
                'items_count' => $cartItems->count(),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
