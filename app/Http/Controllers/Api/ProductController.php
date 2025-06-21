<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with(['category', 'brand', 'productModel', 'images', 'laptopDetails'])->get();
    }

    public function show($id)
    {
        return Product::with(['category', 'brand', 'productModel', 'images', 'laptopDetails'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'product_model_id' => 'required|exists:product_models,id',
        ]);

        $product = Product::create($validated);

        return response()->json($product->load(['category', 'brand', 'productModel', 'images', 'laptopDetails']), 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'image' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'product_model_id' => 'sometimes|exists:product_models,id',
        ]);

        $product->update($validated);

        return response()->json($product->load(['category', 'brand', 'productModel', 'images', 'laptopDetails']));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
