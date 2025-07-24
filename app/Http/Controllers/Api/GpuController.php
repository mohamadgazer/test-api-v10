<?php

namespace App\Http\Controllers\Api;

use App\Models\Gpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

/**
 * @OA\Tag(
 *     name="GPU",
 *     description="API Endpoints for GPUs management"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class GpuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/gpus",
     *     tags={"GPU"},
     *     summary="List GPUs with pagination",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List paginated GPUs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array",
     *                      @OA\Items(ref="#/components/schemas/Gpu")
     *                 ),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Gpu::paginate(request('per_page', 10))
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/gpus",
     *     tags={"GPU"},
     *     summary="Create a new GPU",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="NVIDIA RTX 3080")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="GPU created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="GPU created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Gpu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
            ]);

            $gpu = Gpu::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'GPU created successfully',
                'data' => $gpu
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/gpus/{id}",
     *     tags={"GPU"},
     *     summary="Get a GPU by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="GPU ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="GPU found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/Gpu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GPU not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="GPU not found")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show($id)
    {
        try {
            $gpu = Gpu::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $gpu
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'GPU not found',
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/gpus/{id}",
     *     tags={"GPU"},
     *     summary="Update a GPU by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="GPU ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Updated GPU Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="GPU updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="GPU updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Gpu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GPU not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="GPU not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $gpu = Gpu::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string',
            ]);

            $gpu->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'GPU updated successfully',
                'data' => $gpu
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'GPU not found',
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/gpus/{id}",
     *     tags={"GPU"},
     *     summary="Delete a GPU by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="GPU ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="GPU deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="GPU deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GPU not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="GPU not found")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy($id)
    {
        try {
            $gpu = Gpu::findOrFail($id);
            $gpu->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'GPU deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'GPU not found',
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }
}

/**
 * @OA\Schema(
 *     schema="Gpu",
 *     type="object",
 *     title="GPU Model",
 *     required={"id","name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="NVIDIA RTX 3080"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-24T11:25:39Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-24T11:25:39Z")
 * )
 */
