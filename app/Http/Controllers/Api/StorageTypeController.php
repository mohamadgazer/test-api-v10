<?php

namespace App\Http\Controllers\Api;

use App\Models\StorageType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Storage Types",
 *     description="Operations related to storage type management"
 * )
 */
class StorageTypeController extends Controller
{
    /**
     * Display a paginated list of storage types
     *
     * @OA\Get(
     *     path="/api/storage-types",
     *     summary="List all storage types",
     *     tags={"Storage Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         example=15
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage types retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/StorageTypePaginated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $perPage = request('per_page', 10);
            $storageTypes = StorageType::paginate($perPage);

            return response()->json([
                'message' => 'Storage types retrieved successfully.',
                'data' => $storageTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new storage type
     *
     * @OA\Post(
     *     path="/api/storage-types",
     *     summary="Create new storage type",
     *     tags={"Storage Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="SSD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Storage type created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage type created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/StorageType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "name": {"The name field is required."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:storage_types,name',
            ]);

            $storageType = StorageType::create($validated);

            return response()->json([
                'message' => 'Storage type created successfully.',
                'data' => $storageType,
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display specific storage type
     *
     * @OA\Get(
     *     path="/api/storage-types/{id}",
     *     summary="Get storage type details",
     *     tags={"Storage Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Storage type ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage type retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/StorageType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage type not found.")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $storageType = StorageType::findOrFail($id);

            return response()->json([
                'message' => 'Storage type retrieved successfully.',
                'data' => $storageType,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Storage type not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update storage type details
     *
     * @OA\Put(
     *     path="/api/storage-types/{id}",
     *     summary="Update storage type",
     *     tags={"Storage Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Storage type ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="NVMe SSD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Storage type updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage type updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/StorageType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage type not found. Unable to update.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "name": {"The name field is required."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $storageType = StorageType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|unique:storage_types,name,' . $id,
            ]);

            $storageType->update($validated);

            return response()->json([
                'message' => 'Storage type updated successfully.',
                'data' => $storageType,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Storage type not found. Unable to update.',
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete storage type
     *
     * @OA\Delete(
     *     path="/api/storage-types/{id}",
     *     summary="Delete storage type",
     *     tags={"Storage Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Storage type ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Storage type deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage type deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Storage type not found. Unable to delete.")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $storageType = StorageType::findOrFail($id);
            $storageType->delete();

            return response()->json([
                'message' => 'Storage type deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Storage type not found. Unable to delete.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

/**
 * @OA\Schema(
 *     schema="StorageType",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="SSD"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="StorageTypePaginated",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/StorageType")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string"),
 *         @OA\Property(property="last", type="string"),
 *         @OA\Property(property="prev", type="string", nullable=true),
 *         @OA\Property(property="next", type="string", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer"),
 *         @OA\Property(property="from", type="integer"),
 *         @OA\Property(property="last_page", type="integer"),
 *         @OA\Property(property="path", type="string"),
 *         @OA\Property(property="per_page", type="integer"),
 *         @OA\Property(property="to", type="integer"),
 *         @OA\Property(property="total", type="integer")
 *     )
 * )
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format: Bearer <token>"
 * )
 */