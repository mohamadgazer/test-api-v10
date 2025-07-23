<?php

namespace App\Http\Controllers\Api;

use App\Models\StorageType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class StorageTypeController extends Controller
{
    /**
     * Display a paginated list of storage types.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $perPage = request('per_page', 10);
        $storageTypes = StorageType::paginate($perPage);

        return response()->json([
            'message' => 'Storage types retrieved successfully.',
            'data' => $storageTypes
        ]);
    }

    /**
     * Store a newly created storage type.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:storage_types,name',
        ]);

        $storageType = StorageType::create($validated);

        return response()->json([
            'message' => 'Storage type created successfully.',
            'data' => $storageType,
        ], 201);
    }

    /**
     * Display the specified storage type.
     *
     * @param int $id
     * @return JsonResponse
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
            ], 404);
        }
    }

    /**
     * Update the specified storage type.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
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
            ], 404);
        }
    }

    /**
     * Remove the specified storage type.
     *
     * @param int $id
     * @return JsonResponse
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
            ], 404);
        }
    }
}
