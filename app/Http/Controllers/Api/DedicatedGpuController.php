<?php

namespace App\Http\Controllers\Api;

use App\Models\DedicatedGpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DedicatedGpuController extends Controller
{
    /**
     * Display a paginated list of dedicated GPUs.
     *
     * @queryParam per_page int Number of items per page (default: 10). Example: 15
     * @return \Illuminate\Http\JsonResponse
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
     * Store a newly created dedicated GPU in storage.
     *
     * @bodyParam name string required The name of the GPU. Example: RTX 4090
     * @return \Illuminate\Http\JsonResponse
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
     * Display the specified dedicated GPU.
     *
     * @urlParam id int required The ID of the GPU. Example: 3
     * @return \Illuminate\Http\JsonResponse
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
     * Update the specified dedicated GPU in storage.
     *
     * @urlParam id int required The ID of the GPU. Example: 5
     * @bodyParam name string required The new name of the GPU. Example: GTX 1660 Ti
     * @return \Illuminate\Http\JsonResponse
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
     * Remove the specified dedicated GPU from storage.
     *
     * @urlParam id int required The ID of the GPU. Example: 2
     * @return \Illuminate\Http\JsonResponse
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
