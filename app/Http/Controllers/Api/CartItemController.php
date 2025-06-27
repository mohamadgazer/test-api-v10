<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\CartItemConfiguration;

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
            'configurations.*.value' => 'required_with:configurations'
        ]);
    
        // إنشاء عنصر السلة
        $cartItem = CartItem::create([
            'user_id' => $validated['user_id'],
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity']
        ]);
    
        // حفظ التكوينات إن وُجدت
        if (!empty($validated['configurations'])) {
            foreach ($validated['configurations'] as $config) {
                CartItemConfiguration::create([
                    'cart_item_id' => $cartItem->id,
                    'key' => $config['key'],
                    'value' => $config['value']
                ]);
            }
        }
    
        return response()->json($cartItem->load('configurations'), 201);
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
    
        // تحديث التكوينات (configurations)
        if ($request->has('configurations')) {
            // حذف التكوينات القديمة
            $cartItem->configurations()->delete();
    
            // إنشاء التكوينات الجديدة
            foreach ($validated['configurations'] as $config) {
                CartItemConfiguration::create([
                    'cart_item_id' => $cartItem->id,
                    'key' => $config['key'],
                    'value' => $config['value']
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
}
