<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductImageController extends Controller
{
    public function index()
    {
        return ProductImage::with('product')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'path' => 'required|string',
            'is_main' => 'boolean'
        ]);

        $image = ProductImage::create($validated);

        return response()->json($image, 201);
    }

    public function update(Request $request, $id)
    {
        $image = ProductImage::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'exists:products,id',
            'path' => 'string',
            'is_main' => 'boolean'
        ]);

        $image->update($validated);

        return response()->json($image);
    }

    public function destroy($id)
    {
        $image = ProductImage::findOrFail($id);
        $image->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
