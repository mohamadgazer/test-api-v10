<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\CartItemConfiguration;
use App\Models\Ram;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
    public function index()
    {
         return CartItem::with(['user', 'product', 'configurations'])
        ->paginate(request('per_page', 10));
    }

    public function show($id)
    {
        return CartItem::with(['user', 'product', 'configurations'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'configurations' => 'nullable|array',
            'configurations.*.key' => 'required_with:configurations|string',
            'configurations.*.value' => 'required_with:configurations',
        ]);

        DB::beginTransaction();

        try {
            $finalPrice = 0;

            $cartItem = CartItem::create([
                'user_id'    => $validated['user_id'],
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
                'final_price'=> $finalPrice,
            ]);

            if (!empty($validated['configurations'])) {
                foreach ($validated['configurations'] as $config) {
                    $displayName = null;
                    $price = null;

                    if ($config['key'] === 'ram_id') {
                        $ram = Ram::with('ramType')->find((int) $config['value']);
                        if ($ram) {
                            $displayName = $ram->size_gb . 'GB ' . optional($ram->ramType)->name;
                            $price = (float) $ram->price;
                            $finalPrice += $price;
                        }
                    } elseif ($config['key'] === 'storage_id') {
                        $storage = Storage::with('storageType')->find((int) $config['value']);
                        if ($storage) {
                            $displayName = $storage->size . 'GB ' . optional($storage->storageType)->name;
                            $price = (float) $storage->price;
                            $finalPrice += $price;
                        }
                    }

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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $cartItem = CartItem::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'configurations' => 'nullable|array',
            'configurations.*.key' => 'required_with:configurations|string',
            'configurations.*.value' => 'required_with:configurations'
        ]);

        $cartItem->update($validated);

        $cartItem->configurations()->delete();

        if ($request->has('configurations')) {
            foreach ($validated['configurations'] as $config) {
                $key = $config['key'];
                $value = $config['value'];
                $displayName = $this->generateDisplayName($key, $value);
                $price = $this->getConfigurationPrice($key, $value);

                $cartItem->configurations()->create([
                    'key' => $key,
                    'value' => $value,
                    'display_name' => $displayName,
                    'price' => $price,
                ]);
            }
        }

        return response()->json($cartItem->load('configurations'));
    }

    public function destroy($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Cart item deleted successfully']);
    }

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
            return $ram ? (float) $ram->price : null;
        }

        if ($key === 'storage_id') {
            $storage = Storage::find($value);
            return $storage ? (float) $storage->price : null;
        }

        return null;
    }
}
