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
 * @OA\Tag(
 *     name="Checkout",
 *     description="Checkout operations and order creation"
 * )
 */
class CheckoutController extends Controller
{
    /**
     * Perform checkout and create a new order.
     *
     * @OA\Post(
     *     path="/api/checkout",
     *     summary="Checkout",
     *     description="Convert the authenticated user's cart into a finalized order",
     *     tags={"Checkout"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Shipping address data",
     *         @OA\JsonContent(
     *             required={"address"},
     *             @OA\Property(property="address", type="string", example="123 Main Street, Cairo", maxLength=255)
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Checkout completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Checkout completed"),
     *             @OA\Property(property="order_id", type="integer", example=5),
     *             @OA\Property(property="total", type="number", format="float", example=2599.50),
     *             @OA\Property(property="items_count", type="integer", example=2)
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Cart is empty",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart is empty")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - a valid token is required",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=422,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "address": {"The address field is required."}
     *             })
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server error or insufficient stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Product Laptop X does not have enough stock.")
     *         )
     *     )
     * )
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
