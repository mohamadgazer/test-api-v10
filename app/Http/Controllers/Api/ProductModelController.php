<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProductModelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/product-models",
     *     summary="Get paginated list of product models",
     *     tags={"Product Models"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ProductModelPaginated")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $productModels = ProductModel::paginate(request('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $productModels
        ]);
    }

    /**
     * @OA\Post(
     *     path="/product-models",
     *     summary="Create a new product model",
     *     tags={"Product Models"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Laptop Pro"),
     *             @OA\Property(property="description", type="string", example="High performance laptop", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product model created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ProductModel"),
     *             @OA\Property(property="message", type="string", example="Product model created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
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
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:product_models,name',
                'description' => 'nullable|string'
            ]);

            $productModel = ProductModel::create($validated);

            return response()->json([
                'success' => true,
                'data' => $productModel,
                'message' => 'Product model created successfully.'
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/product-models/{id}",
     *     summary="Get specific product model by ID",
     *     tags={"Product Models"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of product model to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ProductModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product model not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product model not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $productModel = ProductModel::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $productModel
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product model not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Patch(
     *     path="/product-models/{id}",
     *     summary="Update a product model (partial update)",
     *     tags={"Product Models"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of product model to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Laptop Pro Max", nullable=true),
     *             @OA\Property(property="description", type="string", example="Updated description", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product model updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ProductModel"),
     *             @OA\Property(property="message", type="string", example="Product model updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product model not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product model not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
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
    public function update(Request $request, $id)
    {
        try {
            $productModel = ProductModel::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255|unique:product_models,name,'.$productModel->id,
                'description' => 'nullable|string'
            ]);

            $productModel->update($validated);

            return response()->json([
                'success' => true,
                'data' => $productModel,
                'message' => 'Product model updated successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product model not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/product-models/{id}",
     *     summary="Delete a product model",
     *     tags={"Product Models"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of product model to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product model deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product model deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product model not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product model not found")
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
    public function destroy($id)
    {
        try {
            $productModel = ProductModel::findOrFail($id);
            $productModel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product model deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product model not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}