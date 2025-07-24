<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\CartItemConfiguration;
use App\Models\Ram;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Cart Items",
 *     description="APIs for managing shopping cart items"
 * )
 */
class CartItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart-items",
     *     operationId="getCartItems",
     *     tags={"Cart Items"},
     *     summary="Get user's cart items",
     *     description="Returns paginated list of cart items for authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/PaginatedCartItems"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve cart items"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            $cartItems = CartItem::where('user_id', $user->id)
                ->with(['product', 'configurations'])
                ->paginate(request('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $cartItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart items',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/cart-items/{id}",
     *     operationId="getCartItemById",
     *     tags={"Cart Items"},
     *     summary="Get specific cart item",
     *     description="Returns cart item details if it belongs to authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of cart item",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/CartItemWithDetails")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You don't have permission to access this cart item")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart item not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve cart item"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $cartItem = CartItem::with(['product', 'configurations'])->find($id);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($cartItem->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You don\'t have permission to access this cart item'
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'success' => true,
                'data' => $cartItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/cart-items",
     *     operationId="createCartItem",
     *     tags={"Cart Items"},
     *     summary="Add item to cart",
     *     description="Adds a new item to authenticated user's cart",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=3),
     *             @OA\Property(property="quantity", type="integer", example=2, minimum=1),
     *             @OA\Property(
     *                 property="configurations",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="key", type="string", example="ram_id"),
     *                     @OA\Property(property="value", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/CartItemWithDetails"),
     *             @OA\Property(property="message", type="string", example="Item added to cart successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create cart item"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'configurations' => 'nullable|array',
            'configurations.*.key' => 'required_with:configurations|string',
            'configurations.*.value' => 'required_with:configurations',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        try {
            $finalPrice = 0;

            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'final_price' => 0,
            ]);

            if (!empty($request->configurations)) {
                foreach ($request->configurations as $config) {
                    $displayName = $this->generateDisplayName($config['key'], $config['value']);
                    $price = $this->getConfigurationPrice($config['key'], $config['value']);
                    $finalPrice += $price;

                    $cartItem->configurations()->create([
                        'key' => $config['key'],
                        'value' => $config['value'],
                        'display_name' => $displayName,
                        'price' => $price,
                    ]);
                }

                $cartItem->update(['final_price' => $finalPrice]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $cartItem->load('configurations', 'product'),
                'message' => 'Item added to cart successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create cart item',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/cart-items/{id}",
     *     operationId="updateCartItem",
     *     tags={"Cart Items"},
     *     summary="Update cart item",
     *     description="Updates cart item if it belongs to authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of cart item",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quantity", type="integer", example=3, minimum=1),
     *             @OA\Property(
     *                 property="configurations",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="key", type="string", example="ram_id"),
     *                     @OA\Property(property="value", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/CartItemWithDetails"),
     *             @OA\Property(property="message", type="string", example="Cart item updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You don't have permission to update this cart item")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart item not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update cart item"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $cartItem = CartItem::find($id);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($cartItem->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You don\'t have permission to update this cart item'
                ], Response::HTTP_FORBIDDEN);
            }

            $validator = Validator::make($request->all(), [
                'quantity' => 'sometimes|integer|min:1',
                'configurations' => 'nullable|array',
                'configurations.*.key' => 'required_with:configurations|string',
                'configurations.*.value' => 'required_with:configurations',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cartItem->update($request->only(['quantity']));

            // Recreate configurations
            $cartItem->configurations()->delete();

            $finalPrice = 0;

            if ($request->has('configurations')) {
                foreach ($request->configurations as $config) {
                    $displayName = $this->generateDisplayName($config['key'], $config['value']);
                    $price = $this->getConfigurationPrice($config['key'], $config['value']);
                    $finalPrice += $price;

                    $cartItem->configurations()->create([
                        'key' => $config['key'],
                        'value' => $config['value'],
                        'display_name' => $displayName,
                        'price' => $price,
                    ]);
                }

                $cartItem->update(['final_price' => $finalPrice]);
            }

            return response()->json([
                'success' => true,
                'data' => $cartItem->load('configurations'),
                'message' => 'Cart item updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/cart-items/{id}",
     *     operationId="deleteCartItem",
     *     tags={"Cart Items"},
     *     summary="Remove item from cart",
     *     description="Deletes cart item if it belongs to authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of cart item",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cart item deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You don't have permission to delete this cart item")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart item not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete cart item"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $cartItem = CartItem::find($id);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($cartItem->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You don\'t have permission to delete this cart item'
                ], Response::HTTP_FORBIDDEN);
            }

            $cartItem->delete();
            return response()->json([
                'success' => true,
                'message' => 'Cart item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cart item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // 🔧 Helpers

    private function generateDisplayName($key, $value)
    {
        if ($key === 'ram_id') {
            $ram = Ram::with('ramType')->find($value);
            return $ram && $ram->ramType ? "{$ram->size_gb}GB {$ram->ramType->name}" : null;
        }

        if ($key === 'storage_id') {
            $storage = Storage::with('storageType')->find($value);
            return $storage && $storage->storageType ? "{$storage->size}GB {$storage->storageType->name}" : null;
        }

        return null;
    }

    private function getConfigurationPrice($key, $value)
    {
        if ($key === 'ram_id') {
            $ram = Ram::find($value);
            return $ram ? (float) $ram->price : 0;
        }

        if ($key === 'storage_id') {
            $storage = Storage::find($value);
            return $storage ? (float) $storage->price : 0;
        }

        return 0;
    }
}