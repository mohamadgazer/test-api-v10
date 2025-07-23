<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Categories
 *
 * APIs for managing product categories
 */
class CategoryController extends Controller
{
    /**
     * List all categories (paginated)
     *
     * Retrieve a paginated list of categories.
     *
     * @queryParam per_page int Number of results per page. Defaults to 10. Example: 15
     * 
     * @response 200 {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Electronics",
     *       "description": "All electronic items",
     *       "created_at": "2023-07-15T10:00:00Z",
     *       "updated_at": "2023-07-15T10:00:00Z"
     *     }
     *   ],
     *   "first_page_url": "...",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "...",
     *   "links": [...],
     *   "next_page_url": null,
     *   "path": "...",
     *   "per_page": 10,
     *   "prev_page_url": null,
     *   "to": 1,
     *   "total": 1
     * }
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Category::paginate(request('per_page', 10))
        );
    }

    /**
     * Create a new category
     *
     * @bodyParam name string required The name of the category. Example: Laptops
     * @bodyParam description string Optional category description. Example: Devices like notebooks and ultrabooks
     * 
     * @response 201 {
     *   "id": 5,
     *   "name": "Laptops",
     *   "description": "Devices like notebooks and ultrabooks",
     *   "created_at": "2025-07-15T23:20:00.000000Z",
     *   "updated_at": "2025-07-15T23:20:00.000000Z"
     * }
     * 
     * @response 422 {
     *   "message": "Validation failed.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "name": ["The name has already been taken."]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
            ]);

            $category = Category::create($validated);

            return response()->json($category, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong during creation.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a specific category
     *
     * @urlParam id int required The ID of the category. Example: 3
     * 
     * @response 200 {
     *   "id": 3,
     *   "name": "Accessories",
     *   "description": "Headphones, mice, keyboards",
     *   "created_at": "2025-07-15T23:20:00.000000Z",
     *   "updated_at": "2025-07-15T23:20:00.000000Z"
     * }
     * 
     * @response 404 {
     *   "message": "Category not found."
     * }
     */
    public function show($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            return response()->json($category);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found.'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update an existing category
     *
     * @urlParam id int required The ID of the category. Example: 3
     * @bodyParam name string The updated name. Example: Smartphones
     * @bodyParam description string The updated description. Example: Mobile phones and accessories
     * 
     * @response 200 {
     *   "id": 3,
     *   "name": "Smartphones",
     *   "description": "Mobile phones and accessories",
     *   "created_at": "...",
     *   "updated_at": "2025-07-15T23:25:00.000000Z"
     * }
     * 
     * @response 404 {
     *   "message": "Category not found."
     * }
     * 
     * @response 422 {
     *   "message": "Validation failed.",
     *   "errors": { ... }
     * }
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|string',
            ]);

            $category->update($validated);

            return response()->json($category);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong during update.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a category
     *
     * @urlParam id int required The ID of the category. Example: 5
     * 
     * @response 200 {
     *   "message": "Category deleted"
     * }
     * 
     * @response 404 {
     *   "message": "Category not found."
     * }
     */
    public function destroy($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json(['message' => 'Category deleted']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong during deletion.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
