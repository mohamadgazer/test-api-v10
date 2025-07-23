<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\UserLog;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Products
 *
 * APIs for managing products.
 */
class ProductController extends Controller
{
    /**
     * List all products (paginated).
     *
     * @queryParam per_page int Number of results per page. Default: 10.
     *
     * @response 200 {
     *  "data": [...]
     * }
     */
    public function index()
    {
        $products = Product::with([
            'category',
            'brand',
            'images',
            'composite',
            'composite.defaultRam',
            'composite.defaultStorage',
            'composite.rams',
            'composite.storages',
        ])->paginate(request('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Show a specific product.
     *
     * @urlParam id int required The ID of the product.
     *
     * @response 200 {
     *  "id": 1,
     *  "name": "Product Name",
     *  ...
     * }
     */
    public function show($id)
    {
        try {
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

            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Create a new product.
     *
     * @bodyParam name string required
     * @bodyParam description string
     * @bodyParam price number
     * @bodyParam stock int required
     * @bodyParam category_id int required
     * @bodyParam brand_id int required
     * @bodyParam is_composite boolean
     * @bodyParam composite_type string
     * @bodyParam composite_id int
     * @bodyParam images[] file Image files (max 5MB each)
     *
     * @response 201 {
     *  "id": 1,
     *  "name": "New Product",
     *  ...
     * }
     */
    public function store(Request $request)
    {
        try {
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

            return response()->json([
                'success' => true,
                'data' => $product->load(['category', 'brand', 'images', 'composite'])
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Update an existing product.
     *
     * @urlParam id int required
     * @bodyParam (same as store)
     *
     * @response 200 {
     *   "success": true,
     *   "data": { ... }
     * }
     */
    public function update(Request $request, $id)
    {
        try {
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
                foreach ($product->images as $img) {
                    if (Storage::disk('public')->exists(str_replace('storage/', '', $img->path))) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $img->path));
                    }
                    $img->delete();
                }

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

            return response()->json([
                'success' => true,
                'data' => $product->load(['category', 'brand', 'images', 'composite'])
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Delete a product and its images.
     *
     * @urlParam id int required
     *
     * @response 200 {
     *   "message": "Product deleted"
     * }
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            foreach ($product->images as $img) {
                if (Storage::disk('public')->exists(str_replace('storage/', '', $img->path))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $img->path));
                }
                $img->delete();
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted'
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
