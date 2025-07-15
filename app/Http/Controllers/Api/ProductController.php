<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\UserLog;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

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
            'composite.storages',
        ])->paginate(request('per_page', 10));
    }

    public function show($id)
    {
        $product = Product::with([
            'category',
            'brand',
            'images',
            'composite',
        ])->findOrFail($id);

        if ($product->is_composite && $product->composite_type === \App\Models\LaptopDetail::class) {
            $product->composite->load([
                'rams',
                'storages',
                'defaultRam',
                'defaultStorage',
            ]);
        }

        UserLog::create([
            'user_id' => auth()->id(),
            'action' => 'view_product',
            'target_model' => 'Product',
            'target_id' => $product->id,
            'data' => [],
        ]);

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:5120', // 5MB max
            'is_composite'   => 'boolean',
            'composite_type' => 'nullable|string',
            'composite_id'   => 'nullable|integer',
        ]);

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                $image = Image::make($imageFile)->encode('jpg', 85);
                $filename = uniqid('product_') . '.jpg';
                $path = "products/$filename";
                Storage::disk('public')->put($path, $image);
                $product->images()->create([
                    'path' => "storage/$path",
                    'is_main' => $index === 0,
                ]);
            }
        }

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
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:5120',
            'is_composite'   => 'boolean',
            'composite_type' => 'nullable|string',
            'composite_id'   => 'nullable|integer',
        ]);

        $product->update($validated);

        if ($request->hasFile('images')) {
            // حذف الصور القديمة
            foreach ($product->images as $img) {
                if (Storage::disk('public')->exists(str_replace('storage/', '', $img->path))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $img->path));
                }
                $img->delete();
            }

            // رفع الصور الجديدة
            foreach ($request->file('images') as $index => $imageFile) {
                $image = Image::make($imageFile)->encode('jpg', 85);
                $filename = uniqid('product_') . '.jpg';
                $path = "products/$filename";
                Storage::disk('public')->put($path, $image);
                $product->images()->create([
                    'path' => "storage/$path",
                    'is_main' => $index === 0,
                ]);
            }
        }

        return response()->json(
            $product->load(['category', 'brand', 'images', 'composite'])
        );
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        foreach ($product->images as $img) {
            if (Storage::disk('public')->exists(str_replace('storage/', '', $img->path))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $img->path));
            }
            $img->delete();
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
