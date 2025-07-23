<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProductModelController extends Controller
{
    /**
     * Display a paginated list of product models.
     *
     * @queryParam per_page int Number of items per page. Example: 10
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $items = ProductModel::paginate(request('per_page', 10));
        return response()->json($items);
    }

    /**
     * Store a newly created product model in storage.
     *
     * @bodyParam name string required The name of the product model. Example: Laptop
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $productModel = ProductModel::create($validated);

            return response()->json([
                'message' => 'Product model created successfully.',
                'data' => $productModel,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the product model.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product model.
     *
     * @urlParam id int required The ID of the product model. Example: 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $productModel = ProductModel::findOrFail($id);
            return response()->json($productModel);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product model not found.',
            ], 404);
        }
    }

    /**
     * Update the specified product model in storage.
     *
     * @urlParam id int required The ID of the product model. Example: 1
     * @bodyParam name string required The new name of the product model. Example: Laptop Pro
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $productModel = ProductModel::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $productModel->update($validated);

            return response()->json([
                'message' => 'Product model updated successfully.',
                'data' => $productModel,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product model not found.',
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the product model.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product model from storage.
     *
     * @urlParam id int required The ID of the product model. Example: 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $productModel = ProductModel::findOrFail($id);
            $productModel->delete();

            return response()->json([
                'message' => 'Product model deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product model not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the product model.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
