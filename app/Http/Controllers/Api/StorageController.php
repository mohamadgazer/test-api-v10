<?php

namespace App\Http\Controllers\Api;

use App\Models\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class StorageController extends Controller
{
    /**
     * Display a paginated list of storages.
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
            ], 409); // Conflict
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong during deletion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
