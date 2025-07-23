<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * @group Categories
 *
 * Endpoints for managing product categories
 */
class CategoryController extends Controller
{
    /**
     * List all categories (paginated)
     *
     * Retrieve a paginated list of categories.
     *
     * @queryParam per_page int Number of results per page. Defaults to 10. Example: 15
     * @response 200 scenario="Success" {
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
     *   ...
     * }
     */
    public function index()
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
     * @response 201 scenario="Created" {
     *   "id": 5,
     *   "name": "Laptops",
     *   "description": "Devices like notebooks and ultrabooks",
     *   "created_at": "2025-07-15T23:20:00.000000Z",
     *   "updated_at": "2025-07-15T23:20:00.000000Z"
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Get a specific category
     *
     * @urlParam category int required The ID of the category. Example: 3
     * @response 200 scenario="Success" {
     *   "id": 3,
     *   "name": "Accessories",
     *   "description": "Headphones, mice, keyboards",
     *   "created_at": "2025-07-15T23:20:00.000000Z",
     *   "updated_at": "2025-07-15T23:20:00.000000Z"
     * }
     * @response 404 scenario="Not Found" {
     *   "message": "No query results for model [App\\Models\\Category] 999"
     * }
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update an existing category
     *
     * @urlParam category int required The ID of the category. Example: 3
     * @bodyParam name string The updated name. Example: Smartphones
     * @bodyParam description string The updated description. Example: Mobile phones and accessories
     * @response 200 scenario="Updated" {
     *   "id": 3,
     *   "name": "Smartphones",
     *   "description": "Mobile phones and accessories",
     *   "created_at": "...",
     *   "updated_at": "2025-07-15T23:25:00.000000Z"
     * }
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Delete a category
     *
     * @urlParam category int required The ID of the category. Example: 5
     * @response 200 scenario="Deleted" {
     *   "message": "Category deleted"
     * }
     * @response 404 scenario="Not Found" {
     *   "message": "No query results for model [App\\Models\\Category] 999"
     * }
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}
