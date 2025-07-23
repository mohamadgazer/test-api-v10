<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;

/**
 * @group Checkout
 *
 * Handles the checkout process for authenticated users.
 */
class CheckoutController extends Controller
{
    /**
     * Perform checkout and create a new order.
     *
     * Converts the authenticated user's cart into a finalized order.
     *
     * @authenticated
     *
     * @bodyParam address string required The shipping address. Example: 123 Main St, Cairo
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Checkout completed",
     *   "order_id": 5,
     *   "total": 2599.50,
     *   "items_count": 2
     * }
     *
     * @response 400 {
     *   "status": false,
     *   "message": "Cart is empty"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "address": ["The address field is required."]
     *   }
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "error": "Product Laptop X does not have enough stock."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'address' => 'required|string|max:255',
        ]);

        $userId = Auth::id();

        // Fetch cart items
        $cartItems = CartItem::with(['product', 'configurations'])
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $total = 0;

            // Check stock and calculate total
            foreach ($cartItems as $item) {
                $product = $item->product;

                if (!$product || $item->quantity > $product->stock) {
                    throw new Exception("Product {$product->name} does not have enough stock.");
                }

                $total += $item->final_price * $item->quantity;
            }

            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'address' => $validated['address'],
                'status' => 'processing',
            ]);

            // Create order items and configurations
            foreach ($cartItems as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->final_price,
                ]);

                foreach ($item->configurations as $config) {
                    $orderItem->configurations()->create([
                        'key' => $config->key,
                        'value' => $config->value,
                        'display_name' => $config->display_name,
                        'price' => $config->price,
                    ]);
                }

                // Decrease product stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            CartItem::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Checkout completed',
                'order_id' => $order->id,
                'total' => $order->total,
                'items_count' => $cartItems->count(),
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
