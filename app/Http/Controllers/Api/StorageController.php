<?php

namespace App\Http\Controllers\Api;

use App\Models\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Storage",
 *     description="Storage management operations"
 * )
 */
class StorageController extends Controller
{
    /**
     * Display a paginated list of storages.
     *
     * @OA\Get(
     *     path="/api/storages",
     *     summary="Get all storages",
     *     tags={"Storage"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Storages fetched successfully."
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'message' => 'Storages fetched successfully.',
            'data' => Storage::with('storageType')->paginate(request('per_page', 10)),
        ]);
    }

    /**
     * Store a newly created storage.
     *
     * @OA\Post(
     *     path="/api/storages",
     *     summary="Create a new storage",
     *     security={{"bearerAuth":{}}},
     *     tags={"Storage"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"size", "storage_type_id", "price"},
     *             @OA\Property(property="size", type="number", example=256),
     *             @OA\Property(property="storage_type_id", type="integer", example=1),
     *             @OA\Property(property="price", type="number", example=100.5)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Storage created successfully."),
     *     @OA\Response(response=422, description="Validation failed."),
     *     @OA\Response(response=500, description="Server error.")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'size' => 'required|numeric|min:1',
                'storage_type_id' => 'required|exists:storage_types,id',
                'price' => 'required|numeric|min:0',
            ]);

            $storage = Storage::create($validated);

            return response()->json([
                'message' => 'Storage created successfully.',
                'data' => $storage
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong during creation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified storage.
     *
     * @OA\Get(
     *     path="/api/storages/{id}",
     *     summary="Get a specific storage by ID",
     *     tags={"Storage"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Storage fetched successfully."),
     *     @OA\Response(response=404, description="Storage not found.")
     * )
     */
    public function show($id)
    {
        $storage = Storage::with('storageType')->find($id);

        if (!$storage) {
            return response()->json([
                'message' => 'Storage not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Storage fetched successfully.',
            'data' => $storage
        ]);
    }

    /**
     * Update the specified storage.
     *
     * @OA\Put(
     *     path="/api/storages/{id}",
     *     summary="Update an existing storage",
     *     security={{"bearerAuth":{}}},
     *     tags={"Storage"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="size", type="number", example=512),
     *             @OA\Property(property="storage_type_id", type="integer", example=2),
     *             @OA\Property(property="price", type="number", example=120.99)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Storage updated successfully."),
     *     @OA\Response(response=404, description="Storage not found."),
     *     @OA\Response(response=422, description="Validation failed."),
     *     @OA\Response(response=500, description="Server error.")
     * )
     */
    public function update(Request $request, $id)
    {
        $storage = Storage::find($id);

        if (!$storage) {
            return response()->json([
                'message' => 'Storage not found.'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'size' => 'sometimes|numeric|min:1',
                'storage_type_id' => 'sometimes|exists:storage_types,id',
                'price' => 'sometimes|numeric|min:0',
            ]);

            $storage->update($validated);

            return response()->json([
                'message' => 'Storage updated successfully.',
                'data' => $storage
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong during update.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified storage.
     *
     * @OA\Delete(
     *     path="/api/storages/{id}",
     *     summary="Delete a storage",
     *     security={{"bearerAuth":{}}},
     *     tags={"Storage"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Storage deleted successfully."),
     *     @OA\Response(response=404, description="Storage not found."),
     *     @OA\Response(response=409, description="Conflict: cannot delete."),
     *     @OA\Response(response=500, description="Server error.")
     * )
     */
    public function destroy($id)
    {
        $storage = Storage::find($id);

        if (!$storage) {
            return response()->json([
                'message' => 'Storage not found.'
            ], 404);
        }

        try {
            $storage->delete();

            return response()->json([
                'message' => 'Storage deleted successfully.'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Cannot delete storage: it is being used elsewhere.',
                'error' => $e->getMessage(),
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong during deletion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
