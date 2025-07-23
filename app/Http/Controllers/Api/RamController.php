<?php

namespace App\Http\Controllers\Api;

use App\Models\Ram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RamController extends Controller
{
    /**
     * @OA\Get(
     *     path="/rams",
     *     summary="Get paginated list of RAMs",
     *     tags={"RAM"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/RamPaginated")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $rams = Ram::with('ramType')->paginate(request('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $rams
        ]);
    }

    /**
     * @OA\Get(
     *     path="/rams/{id}",
     *     summary="Get a specific RAM by ID",
     *     tags={"RAM"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of RAM to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/RamWithType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RAM not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RAM not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $ram = Ram::with('ramType')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $ram
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'RAM not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Post(
     *     path="/rams",
     *     summary="Create a new RAM entry",
     *     tags={"RAM"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RamCreate")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="RAM created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/RamWithType"),
     *             @OA\Property(property="message", type="string", example="RAM created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or duplicate entry",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'size_gb' => 'required|numeric|min:1',
                'price' => 'required|numeric|min:0',
                'ram_type_id' => 'required|exists:ram_types,id',
            ]);

            // Check for existing RAM with same type and size
            $existingRam = Ram::where('size_gb', $validated['size_gb'])
                ->where('ram_type_id', $validated['ram_type_id'])
                ->first();

            if ($existingRam) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'size_gb' => ['A RAM with this size and type already exists.']
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $ram = Ram::create($validated);

            return response()->json([
                'success' => true,
                'data' => $ram->load('ramType'),
                'message' => 'RAM created successfully'
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @OA\Put(
     *     path="/rams/{id}",
     *     summary="Update an existing RAM entry",
     *     tags={"RAM"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of RAM to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RamCreate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RAM updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/RamWithType"),
     *             @OA\Property(property="message", type="string", example="RAM updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RAM not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RAM not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or duplicate entry",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $ram = Ram::findOrFail($id);

            $validated = $request->validate([
                'size_gb' => 'required|numeric|min:1',
                'price' => 'required|numeric|min:0',
                'ram_type_id' => 'required|exists:ram_types,id',
            ]);

            // Check for existing RAM with same type and size (excluding current one)
            $existingRam = Ram::where('size_gb', $validated['size_gb'])
                ->where('ram_type_id', $validated['ram_type_id'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingRam) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'size_gb' => ['A RAM with this size and type already exists.']
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $ram->update($validated);

            return response()->json([
                'success' => true,
                'data' => $ram->load('ramType'),
                'message' => 'RAM updated successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'RAM not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @OA\Delete(
     *     path="/rams/{id}",
     *     summary="Delete a RAM entry",
     *     tags={"RAM"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of RAM to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RAM deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="RAM deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RAM not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RAM not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $ram = Ram::findOrFail($id);
            $ram->delete();

            return response()->json([
                'success' => true,
                'message' => 'RAM deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'RAM not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}