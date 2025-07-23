<?php

namespace App\Http\Controllers\Api;

use App\Models\RamType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class RamTypeController extends Controller
{
    /**
     * Display a paginated list of RAM types.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $data = RamType::paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'RAM Types fetched successfully.',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return $this->serverError($e);
        }
    }

    /**
     * Store a newly created RAM type.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:ram_types,name',
            ]);

            $ramType = RamType::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'RAM Type created successfully.',
                'data' => $ramType,
            ], 201);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (Exception $e) {
            return $this->serverError($e);
        }
    }

    /**
     * Display the specified RAM type.
     */
    public function show($id)
    {
        try {
            $ramType = RamType::findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'RAM Type retrieved successfully.',
                'data' => $ramType,
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('RAM Type not found.');
        } catch (Exception $e) {
            return $this->serverError($e);
        }
    }

    /**
     * Update the specified RAM type.
     */
    public function update(Request $request, $id)
    {
        try {
            $ramType = RamType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|unique:ram_types,name,' . $id,
            ]);

            $ramType->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'RAM Type updated successfully.',
                'data' => $ramType,
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('RAM Type not found.');
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (Exception $e) {
            return $this->serverError($e);
        }
    }

    /**
     * Remove the specified RAM type from storage.
     */
    public function destroy($id)
    {
        try {
            $ramType = RamType::findOrFail($id);
            $ramType->delete();

            return response()->json([
                'status' => true,
                'message' => 'RAM Type deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('RAM Type not found.');
        } catch (Exception $e) {
            return $this->serverError($e);
        }
    }

    // ========== ✅ مساعدات داخلية لتهذيب الكود ==========

    private function validationError(ValidationException $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
        ], 422);
    }

    private function notFound($message = 'Resource not found.')
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], 404);
    }

    private function serverError(Exception $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Server error occurred.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
