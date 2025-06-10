<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'address' => 'required|string|max:255',
        ]);

        $userId = $request->user_id;

        // هات كل المنتجات اللي في السلة
        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            // حساب إجمالي السعر
            $total = 0;

            foreach ($cartItems as $item) {
                $total += $item->product->price * $item->quantity;

                // تأكد من توفر الكمية
                if ($item->quantity > $item->product->stock) {
                    throw new \Exception("Product {$item->product->name} does not have enough stock.");
                }
            }

            // إنشاء Order
            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'address' => $request->address,
                'status' => 'processing',
            ]);

            // إنشاء OrderItems وتحديث المخزون
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            // مسح السلة
            CartItem::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json(['message' => 'Checkout completed', 'order_id' => $order->id], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
