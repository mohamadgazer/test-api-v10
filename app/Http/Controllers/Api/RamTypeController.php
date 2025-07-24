<?php

namespace App\Http\Controllers\Api;

use App\Models\RamType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

/**
 * @OA\Tag(
 *     name="RAM Types",
 *     description="Operations related to RAM types"
 * )
 */
class RamTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ram-types",
     *     summary="List RAM types",
     *     description="Get a paginated list of all RAM types",
     *     tags={"RAM Types"},
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
     *         description="List retrieved successfully"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/ram-types",
     *     summary="Create RAM type",
     *     description="Create a new RAM type",
     *     tags={"RAM Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="DDR5")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
     * @OA\Get(
     *     path="/api/ram-types/{id}",
     *     summary="Get RAM type",
     *     description="Retrieve a RAM type by ID",
     *     tags={"RAM Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Retrieved successfully"),
     *     @OA\Response(response=404, description="Not found")
     * )
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
     * @OA\Put(
     *     path="/api/ram-types/{id}",
     *     summary="Update RAM type",
     *     description="Update a RAM type by ID",
     *     tags={"RAM Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="DDR4")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Not found")
     * )
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
     * @OA\Delete(
     *     path="/api/ram-types/{id}",
     *     summary="Delete RAM type",
     *     description="Delete a RAM type by ID",
     *     tags={"RAM Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Deleted successfully"),
     *     @OA\Response(response=404, description="Not found")
     * )
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

    // ========== ✅ Helpers ==========

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
