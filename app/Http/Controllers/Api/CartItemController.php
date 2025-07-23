<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\CartItemConfiguration;
use App\Models\Ram;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @group Cart Items
 *
 * APIs for managing items inside user's shopping cart
 */
class CartItemController extends Controller
{
    /**
     * Get all cart items (paginated)
     *
     * @queryParam per_page int Number of items per page. Example: 10
     * @response 200 scenario="Success" {
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
        return response()->json(
            CartItem::with(['user', 'product', 'configurations'])->paginate(request('per_page', 10)),
            200
        );
    }

    /**
     * Get a specific cart item
     *
     * @urlParam id int required ID of the cart item. Example: 1
     * @response 200 {
     *   "id": 1,
     *   "user_id": 1,
     *   "product_id": 5,
     *   "quantity": 2,
     *   "final_price": 1500.00,
     *   "configurations": [...]
     * }
     * @response 404 {
     *   "message": "Cart item not found"
     * }
     */
    public function show($id)
    {
        $item = CartItem::with(['user', 'product', 'configurations'])->find($id);
        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        return response()->json($item);
    }

    /**
     * Add new item to cart
     *
     * @bodyParam user_id int required User ID. Example: 1
     * @bodyParam product_id int required Product ID. Example: 3
     * @bodyParam quantity int required Quantity of the product. Minimum: 1. Example: 2
     * @bodyParam configurations array optional List of configurations
     * @bodyParam configurations[].key string required Configuration key (e.g. ram_id). Example: ram_id
     * @bodyParam configurations[].value mixed required Configuration value (e.g. 2). Example: 2
     *
     * @response 201 {
     *   "id": 1,
     *   "user_id": 1,
     *   "product_id": 3,
     *   "quantity": 2,
     *   "final_price": 1200,
     *   "configurations": [...]
     * }
     * @response 422 {
     *   "message": "Validation error",
     *   "errors": { ... }
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'configurations' => 'nullable|array',
            'configurations.*.key' => 'required_with:configurations|string',
            'configurations.*.value' => 'required_with:configurations',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $finalPrice = 0;

            $cartItem = CartItem::create([
                'user_id' => $request->user_id,
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
            return response()->json($cartItem->load('configurations', 'product'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create cart item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a cart item
     *
     * @urlParam id int required Cart item ID. Example: 1
     * @bodyParam quantity int Updated quantity. Example: 3
     * @bodyParam configurations array optional List of configurations to update
     *
     * @response 200 {
     *   "id": 1,
     *   "quantity": 3,
     *   "configurations": [...]
     * }
     * @response 404 {
     *   "message": "Cart item not found"
     * }
     */
    public function update(Request $request, $id)
    {
        $cartItem = CartItem::find($id);
        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|integer|min:1',
            'configurations' => 'nullable|array',
            'configurations.*.key' => 'required_with:configurations|string',
            'configurations.*.value' => 'required_with:configurations',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
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

        return response()->json($cartItem->load('configurations'));
    }

    /**
     * Delete a cart item
     *
     * @urlParam id int required ID of the cart item to delete. Example: 5
     * @response 200 {
     *   "message": "Cart item deleted successfully"
     * }
     * @response 404 {
     *   "message": "Cart item not found"
     * }
     */
    public function destroy($id)
    {
        $cartItem = CartItem::find($id);
        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->delete();
        return response()->json(['message' => 'Cart item deleted successfully']);
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
