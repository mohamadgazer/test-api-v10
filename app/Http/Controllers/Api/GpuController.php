<?php

namespace App\Http\Controllers\Api;

use App\Models\Gpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class GpuController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Gpu::paginate(request('per_page', 10))
        ]);
    }

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
