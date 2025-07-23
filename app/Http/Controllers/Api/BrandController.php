<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * @group Brand Management
 *
 * APIs for managing product brands
 */
class BrandController extends Controller
{
    /**
     * Get all brands
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "name": "Apple",
     *     "logo": "https://example.com/apple.png",
     *     "created_at": "2025-07-15T23:55:00.000000Z",
     *     "updated_at": "2025-07-15T23:55:00.000000Z"
     *   }
     * ]
     */
    public function index()
    {
        return response()->json(Brand::all(), 200);
    }

    /**
     * Create a new brand
     *
     * @bodyParam name string required Brand name. Example: Samsung
     * @bodyParam logo string The logo URL (optional). Example: https://example.com/samsung.png
     *
     * @response 201 {
     *   "id": 3,
     *   "name": "Samsung",
     *   "logo": "https://example.com/samsung.png",
     *   "created_at": "2025-07-15T23:57:00.000000Z",
     *   "updated_at": "2025-07-15T23:57:00.000000Z"
     * }
     *
     * @response 422 {
     *   "message": "Validation error",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $brand = Brand::create($validator->validated());

        return response()->json($brand, 201);
    }

    /**
     * Get a specific brand
     *
     * @urlParam id integer required The ID of the brand. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Apple",
     *   "logo": "https://example.com/apple.png",
     *   "created_at": "2025-07-15T23:58:00.000000Z",
     *   "updated_at": "2025-07-15T23:58:00.000000Z"
     * }
     *
     * @response 404 {
     *   "message": "Brand not found"
     * }
     */
    public function show($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        return response()->json($brand);
    }

    /**
     * Update an existing brand
     *
     * @urlParam id integer required Brand ID. Example: 2
     * @bodyParam name string required Updated brand name. Example: Lenovo
     * @bodyParam logo string The new logo URL. Example: https://example.com/lenovo.png
     *
     * @response 200 {
     *   "id": 2,
     *   "name": "Lenovo",
     *   "logo": "https://example.com/lenovo.png",
     *   "created_at": "...",
     *   "updated_at": "2025-07-15T23:59:00.000000Z"
     * }
     *
     * @response 404 {
     *   "message": "Brand not found"
     * }
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $brand->update($validator->validated());

        return response()->json($brand);
    }

    /**
     * Delete a brand
     *
     * @urlParam id integer required Brand ID to delete. Example: 2
     *
     * @response 200 {
     *   "message": "Deleted successfully"
     * }
     *
     * @response 404 {
     *   "message": "Brand not found"
     * }
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $brand->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
