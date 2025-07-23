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
     * Display a paginated listing of the RAMs with their types.
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Store a newly created RAM entry in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'size_gb' => 'required|numeric|min:1',
                'price' => 'required|numeric|min:0',
                'ram_type_id' => 'required|exists:ram_types,id',
            ]);

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
     * Update the specified RAM entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
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
     * Remove the specified RAM entry from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
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
