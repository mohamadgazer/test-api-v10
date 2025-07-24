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
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for Product Management"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List all products (paginated)",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Product")
     *                 ),
     *                 @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *                 @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Show a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductWithDetails")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
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
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "stock", "category_id", "brand_id"},
     *                 @OA\Property(property="name", type="string", example="New Product"),
     *                 @OA\Property(property="description", type="string", example="Product description"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="stock", type="integer", example=100),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="brand_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Array of image files (max 5MB each)"
     *                 ),
     *                 @OA\Property(property="is_composite", type="boolean", example=false),
     *                 @OA\Property(property="composite_type", type="string", example=null),
     *                 @OA\Property(property="composite_id", type="integer", example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create product")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'images' => 'nullable|array|max:5',
                'images.*' => 'file|image|mimes:jpeg,png,jpg,gif|max:5120',
                'is_composite' => 'boolean',
                'composite_type' => 'nullable|string',
                'composite_id' => 'nullable|integer',
            ]);

            $product = Product::create($validated);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $imageFile) {
                    $this->processAndStoreImage($product, $imageFile, $index === 0);
                }
            }

            UserLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_product',
                'target_model' => 'Product',
                'target_id' => $product->id,
                'data' => $validated,
            ]);

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update an existing product",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "stock", "category_id", "brand_id"},
     *                 @OA\Property(property="name", type="string", example="Updated Product"),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="price", type="number", format="float", example=109.99),
     *                 @OA\Property(property="stock", type="integer", example=50),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="brand_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Array of image files (max 5MB each)"
     *                 ),
     *                 @OA\Property(property="is_composite", type="boolean", example=false),
     *                 @OA\Property(property="composite_type", type="string", example=null),
     *                 @OA\Property(property="composite_id", type="integer", example=null),
     *                 @OA\Property(property="remove_images", type="array", @OA\Items(type="integer"), description="Array of image IDs to remove")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update product")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'images' => 'nullable|array|max:5',
                'images.*' => 'file|image|mimes:jpeg,png,jpg,gif|max:5120',
                'is_composite' => 'boolean',
                'composite_type' => 'nullable|string',
                'composite_id' => 'nullable|integer',
                'remove_images' => 'nullable|array',
                'remove_images.*' => 'integer|exists:product_images,id',
            ]);

            $product->update($validated);

            // Handle image removal
            if (!empty($validated['remove_images'])) {
                $this->deleteImages($product, $validated['remove_images']);
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $imageFile) {
                    $this->processAndStoreImage($product, $imageFile, false);
                }
            }

            // Ensure at least one main image exists
            if ($product->images()->where('is_main', true)->doesntExist()) {
                $firstImage = $product->images()->first();
                if ($firstImage) {
                    $firstImage->update(['is_main' => true]);
                }
            }

            UserLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_product',
                'target_model' => 'Product',
                'target_id' => $product->id,
                'data' => $validated,
            ]);

            return response()->json([
                'success' => true,
                'data' => $product->load(['category', 'brand', 'images', 'composite'])
            ]);
        } catch (ModelNotFoundException $e) {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete product")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            // Delete all associated images
            $this->deleteImages($product, $product->images->pluck('id')->toArray());

            $product->delete();

            UserLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_product',
                'target_model' => 'Product',
                'target_id' => $id,
                'data' => [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Process and store an image for a product
     *
     * @param Product $product
     * @param UploadedFile $imageFile
     * @param bool $isMain
     * @return void
     */
    private function processAndStoreImage($product, $imageFile, $isMain = false)
    {
        $image = Image::make($imageFile)->encode('jpg', 85);
        $filename = uniqid('product_') . '.jpg';
        $path = "products/$filename";
        Storage::disk('public')->put($path, $image);
        
        $product->images()->create([
            'path' => "storage/$path",
            'is_main' => $isMain,
        ]);
    }

    /**
     * Delete specific images for a product
     *
     * @param Product $product
     * @param array $imageIds
     * @return void
     */
    private function deleteImages($product, $imageIds)
    {
        $imagesToDelete = $product->images()->whereIn('id', $imageIds)->get();
        
        foreach ($imagesToDelete as $img) {
            try {
                $filePath = str_replace('storage/', '', $img->path);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $img->delete();
            } catch (\Exception $e) {
                // Log error but don't stop the process
                \Log::error("Failed to delete image {$img->id}: " . $e->getMessage());
            }
        }
    }
}