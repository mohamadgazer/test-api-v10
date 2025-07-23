<?php

namespace App\Http\Controllers\Api;

use App\Models\Cpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CpuController extends Controller
{
    /**
     * Get a paginated list of CPUs.
     *
     * Retrieve all CPUs in a paginated format.
     *
     * @queryParam per_page int Number of results per page. Defaults to 10. Example: 15
     * @response 200 {
     *   "status": true,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "Intel Core i7",
     *         "created_at": "2025-07-15T20:41:00.000000Z",
     *         "updated_at": "2025-07-15T20:41:00.000000Z"
     *       }
     *     ],
     *     ...
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        $cpus = Cpu::paginate(request('per_page', 10));

        return response()->json([
            'status' => true,
            'data' => $cpus,
        ]);
    }

    /**
     * Store a new CPU.
     *
     * Create a new CPU with a name.
     *
     * @bodyParam name string required The name of the CPU. Example: Intel Core i9-13900K
     * @response 201 {
     *   "status": true,
     *   "message": "CPU created successfully.",
     *   "data": {
     *     "id": 1,
     *     "name": "Intel Core i9-13900K",
     *     "created_at": "...",
     *     "updated_at": "..."
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": [
     *       "The name field is required."
     *     ]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $cpu = Cpu::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'CPU created successfully.',
            'data' => $cpu,
        ], 201);
    }

    /**
     * Get a specific CPU.
     *
     * Show details for a CPU by ID.
     *
     * @urlParam id int required The ID of the CPU. Example: 1
     * @response 200 {
     *   "status": true,
     *   "data": {
     *     "id": 1,
     *     "name": "Intel Core i7",
     *     "created_at": "...",
     *     "updated_at": "..."
     *   }
     * }
     * @response 404 {
     *   "status": false,
     *   "message": "CPU not found."
     * }
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cpu = Cpu::findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $cpu,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'CPU not found.',
            ], 404);
        }
    }

    /**
     * Update a CPU.
     *
     * Modify the name of a specific CPU.
     *
     * @urlParam id int required The ID of the CPU. Example: 1
     * @bodyParam name string required The new name of the CPU. Example: AMD Ryzen 9 7950X
     * @response 200 {
     *   "status": true,
     *   "message": "CPU updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "name": "AMD Ryzen 9 7950X",
     *     "created_at": "...",
     *     "updated_at": "..."
     *   }
     * }
     * @response 404 {
     *   "status": false,
     *   "message": "CPU not found."
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $cpu = Cpu::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $cpu->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'CPU updated successfully.',
                'data' => $cpu,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'CPU not found.',
            ], 404);
        }
    }

    /**
     * Delete a CPU.
     *
     * Remove a CPU from the system.
     *
     * @urlParam id int required The ID of the CPU. Example: 1
     * @response 200 {
     *   "status": true,
     *   "message": "CPU deleted successfully."
     * }
     * @response 404 {
     *   "status": false,
     *   "message": "CPU not found."
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $cpu = Cpu::findOrFail($id);
            $cpu->delete();

            return response()->json([
                'status' => true,
                'message' => 'CPU deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'CPU not found.',
            ], 404);
        }
    }
}
