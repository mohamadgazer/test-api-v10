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
 * @group Cart Items
 *
 * APIs for managing items inside user's shopping cart
 */
class CartItemController extends Controller
{
    /**
     * Get all cart items for authenticated user (paginated)
     *
     * @authenticated
     * @queryParam per_page int Number of items per page. Example: 10
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "product_id": 5,
     *       "quantity": 2,
     *       "final_price": 1500.00
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $user = Auth::user();
        
        $cartItems = CartItem::where('user_id', $user->id)
            ->with(['product', 'configurations'])
            ->paginate(request('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $cartItems
        ]);
    }

    /**
     * Get a specific cart item (only if belongs to authenticated user)
     *
     * @authenticated
     * @urlParam id int required ID of the cart item. Example: 1
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "product_id": 5,
     *     "quantity": 2,
     *     "final_price": 1500.00,
     *     "configurations": [...]
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You don't have permission to access this cart item"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Cart item not found"
     * }
     */
    public function show($id)
    {
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
    }

    /**
     * Add new item to cart (for authenticated user)
     *
     * @authenticated
     * @bodyParam product_id int required Product ID. Example: 3
     * @bodyParam quantity int required Quantity of the product. Minimum: 1. Example: 2
     * @bodyParam configurations array optional List of configurations
     * @bodyParam configurations[].key string required Configuration key (e.g. ram_id). Example: ram_id
     * @bodyParam configurations[].value mixed required Configuration value (e.g. 2). Example: 2
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "product_id": 3,
     *     "quantity": 2,
     *     "final_price": 1200,
     *     "configurations": [...]
     *   },
     *   "message": "Item added to cart successfully"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": { ... }
     * }
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
     * Update a cart item (only if belongs to authenticated user)
     *
     * @authenticated
     * @urlParam id int required Cart item ID. Example: 1
     * @bodyParam quantity int Updated quantity. Example: 3
     * @bodyParam configurations array optional List of configurations to update
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "quantity": 3,
     *     "configurations": [...]
     *   },
     *   "message": "Cart item updated successfully"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You don't have permission to update this cart item"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Cart item not found"
     * }
     */
    public function update(Request $request, $id)
    {
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
    }

    /**
     * Delete a cart item (only if belongs to authenticated user)
     *
     * @authenticated
     * @urlParam id int required ID of the cart item to delete. Example: 5
     * @response 200 {
     *   "success": true,
     *   "message": "Cart item deleted successfully"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You don't have permission to delete this cart item"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Cart item not found"
     * }
     */
    public function destroy($id)
    {
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