<?php

namespace App\Http\Controllers\Api;

use App\Models\DedicatedGpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Dedicated GPUs",
 *     description="Operations related to dedicated graphics processing units"
 * )
 */
class DedicatedGpuController extends Controller
{
    /**
     * Display a paginated list of dedicated GPUs
     *
     * @OA\Get(
     *     path="/api/dedicated-gpus",
     *     summary="List all dedicated GPUs",
     *     tags={"Dedicated GPUs"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         example=15
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/DedicatedGpu")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 description="Pagination links"
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 description="Pagination metadata"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to retrieve GPUs."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */

    public function index()
    {
        try {
            $gpus = DedicatedGpu::paginate(request('per_page', 10));
            return response()->json($gpus);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve GPUs.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
     * Store a newly created dedicated GPU
     *
     * @OA\Post(
     *     path="/api/dedicated-gpus",
     *     summary="Create a new dedicated GPU",
     *     tags={"Dedicated GPUs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="RTX 4090")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="GPU created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dedicated GPU created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/DedicatedGpu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to create GPU."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $gpu = DedicatedGpu::create($validated);

            return response()->json([
                'message' => 'Dedicated GPU created successfully.',
                'data' => $gpu,
            ], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create GPU.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


   /**
     * Store a newly created dedicated GPU
     *
     * @OA\Post(
     *     path="/api/dedicated-gpus",
     *     summary="Create a new dedicated GPU",
     *     tags={"Dedicated GPUs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="RTX 4090")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="GPU created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dedicated GPU created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/DedicatedGpu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to create GPU."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $gpu = DedicatedGpu::findOrFail($id);
            return response()->json($gpu);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dedicated GPU not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving GPU.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified dedicated GPU
     *
     * @OA\Put(
     *     path="/api/dedicated-gpus/{id}",
     *     summary="Update a GPU",
     *     tags={"Dedicated GPUs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the GPU to update",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=5
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="GTX 1660 Ti")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="GPU updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dedicated GPU updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/DedicatedGpu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GPU not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dedicated GPU not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to update GPU."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $gpu = DedicatedGpu::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $gpu->update($validated);

            return response()->json([
                'message' => 'Dedicated GPU updated successfully.',
                'data' => $gpu,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dedicated GPU not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update GPU.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Remove the specified dedicated GPU
     *
     * @OA\Delete(
     *     path="/api/dedicated-gpus/{id}",
     *     summary="Delete a GPU",
     *     tags={"Dedicated GPUs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the GPU to delete",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=2
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="GPU deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dedicated GPU deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GPU not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dedicated GPU not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete GPU."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $gpu = DedicatedGpu::findOrFail($id);
            $gpu->delete();

            return response()->json([
                'message' => 'Dedicated GPU deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dedicated GPU not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete GPU.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

/**
 * @OA\Schema(
 *     schema="DedicatedGpu",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="RTX 4090"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */