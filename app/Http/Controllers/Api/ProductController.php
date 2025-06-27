<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with([ 
             'category',
        'brand',
        'images',
        'composite',
        'composite.defaultRam',
        'composite.defaultStorage',
        'composite.rams',
        'composite.storages',])->get();
    }

    public function show($id)
    {
        $product = Product::with([
            'category',
            'brand',
            'images',
            'composite',
        ])->findOrFail($id);
    
        // لو المنتج مركب من LaptopDetail، نحمل العلاقات الإضافية
        if ($product->is_composite && $product->composite_type === \App\Models\LaptopDetail::class) {
            $product->composite->load([
                'rams',
                'storages',
                'defaultRam',
                'defaultStorage',
            ]);
        }
    
        return response()->json($product);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',

            'is_composite'   => 'boolean',
            'composite_type' => 'nullable|string',
            'composite_id'   => 'nullable|integer',
        ]);

        $product = Product::create($validated);

        return response()->json(
            $product->load(['category', 'brand', 'images', 'composite']),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',

            'is_composite'   => 'boolean',
            'composite_type' => 'nullable|string',
            'composite_id'   => 'nullable|integer',
        ]);

        $product->update($validated);

        return response()->json(
            $product->load(['category', 'brand', 'images', 'composite'])
        );
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
