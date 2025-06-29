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
        return CartItem::with(['user', 'product', 'configurations'])->get();
    }

    public function show($id)
    {
        return CartItem::with(['user', 'product', 'configurations'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'configurations' => 'nullable|array',
            'configurations.*.key' => 'required_with:configurations|string',
            'configurations.*.value' => 'required_with:configurations',
        ]);
    
        DB::beginTransaction();
    
        try {
            $finalPrice = 0;
            $cartItem = CartItem::create([
                'user_id' => $validated['user_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'final_price' => 0, // مؤقتًا
            ]);
    
            $detailedConfigs = [];
    
            foreach ($validated['configurations'] ?? [] as $config) {
                $displayName = null;
                $fullData = null;
    
                if ($config['key'] === 'ram_id') {
                    $ram = Ram::with('ramType')->find((int)$config['value']);
                    if ($ram) {
                        $displayName = "{$ram->size_gb}GB " . ($ram->ramType->name ?? '');
                        $finalPrice += (float)$ram->price;
                        $fullData = $ram->toArray();
                    }
                } elseif ($config['key'] === 'storage_id') {
                    $storage = Storage::with('storageType')->find((int)$config['value']);
                    if ($storage) {
                        $displayName = "{$storage->size}GB " . ($storage->storageType->name ?? '');
                        $finalPrice += (float)$storage->price;
                        $fullData = $storage->toArray();
                    }
                }
    
                // سجل التكوين
                $cartItem->configurations()->create([
                    'key' => $config['key'],
                    'value' => $config['value'],
                    'display_name' => $displayName,
                ]);
    
                // سجّل نسخة كاملة للإرجاع
                $detailedConfigs[] = [
                    'key' => $config['key'],
                    'value' => $config['value'],
                    'display_name' => $displayName,
                    'full_data' => $fullData,
                ];
            }
    
            $cartItem->update(['final_price' => $finalPrice]);
    
            DB::commit();
    
            return response()->json([
                'cart_item' => $cartItem->load('configurations', 'product'),
                'detailed_configurations' => $detailedConfigs,
            ], 201);
    
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

        // حذف التكوينات القديمة
        $cartItem->configurations()->delete();

        // إضافة التكوينات الجديدة
        if ($request->has('configurations')) {
            foreach ($validated['configurations'] as $config) {
                $key = $config['key'];
                $value = $config['value'];
                $displayName = $this->generateDisplayName($key, $value);

                $cartItem->configurations()->create([
                    'key' => $key,
                    'value' => $value,
                    'display_name' => $displayName
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

    /**
     * توليد display_name حسب نوع العنصر.
     */
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
}
