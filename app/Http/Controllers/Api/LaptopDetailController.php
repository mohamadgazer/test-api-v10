<?php

namespace App\Http\Controllers\Api;

use App\Models\LaptopDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Laptop Details",
 *     description="APIs for managing laptop specifications including hardware components and pricing"
 * )
 */
class LaptopDetailController extends Controller
{
    /**
     * List all laptop details (paginated)
     *
     * @OA\Get(
     *     path="/api/laptop-details",
     *     summary="List all laptop specifications",
     *     tags={"Laptop Details"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         example=15
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/LaptopDetail")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unexpected error occurred"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $data = LaptopDetail::with([
                'productModel',
                'rams',
                'storages',
                'defaultRam',
                'defaultStorage',
                'cpu',
                'gpu',
                'dedicatedGpu',
                'storageTypes',
            ])->paginate(request('per_page', 10));

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create new laptop specification
     *
     * @OA\Post(
     *     path="/api/laptop-details",
     *     summary="Create new laptop configuration",
     *     tags={"Laptop Details"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_model_id","brand_id","cpu_id","base_price"},
     *             @OA\Property(property="product_model_id", type="integer", example=2),
     *             @OA\Property(property="brand_id", type="integer", example=3),
     *             @OA\Property(property="cpu_id", type="integer", example=2),
     *             @OA\Property(property="gpu_id", type="integer", nullable=true, example=2),
     *             @OA\Property(property="dedicated_gpu_id", type="integer", nullable=true, example=2),
     *             @OA\Property(property="base_price", type="number", format="float", example=15000),
     *             @OA\Property(property="default_ram_id", type="integer", nullable=true),
     *             @OA\Property(property="default_storage_id", type="integer", nullable=true),
     *             @OA\Property(property="ram_type_id", type="integer", nullable=true),
     *             @OA\Property(
     *                 property="storage_type_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1,2}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laptop detail created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LaptopDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "product_model_id": {"The product model id field is required"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unexpected error occurred"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_model_id' => 'required|exists:product_models,id',
                'brand_id' => 'required|exists:brands,id',
                'cpu_id' => 'required|exists:cpus,id',
                'gpu_id' => 'nullable|exists:gpus,id',
                'dedicated_gpu_id' => 'nullable|exists:dedicated_gpus,id',
                'base_price' => 'required|numeric|min:0',
                'default_ram_id' => 'nullable|exists:rams,id',
                'default_storage_id' => 'nullable|exists:storages,id',
                'ram_type_id' => 'nullable|exists:ram_types,id',
                'storage_type_ids' => 'nullable|array',
                'storage_type_ids.*' => 'exists:storage_types,id',
            ]);

            $detail = LaptopDetail::create($validated);

            // Sync relations
            $storageTypeIds = $request->input('storage_type_ids', []);
            $detail->storageTypes()->sync($storageTypeIds);

            $availableStorageIds = \App\Models\Storage::whereIn('storage_type_id', $storageTypeIds)->pluck('id')->toArray();
            $detail->storages()->sync($availableStorageIds);

            // Set default_storage_id if not provided but storages exist
            if (empty($validated['default_storage_id']) && count($availableStorageIds)) {
                $detail->default_storage_id = \App\Models\Storage::whereIn('id', $availableStorageIds)->orderBy('price')->value('id');
            }

            $ramTypeId = $validated['ram_type_id'] ?? null;
            $availableRamIds = $ramTypeId
                ? \App\Models\Ram::where('ram_type_id', $ramTypeId)->pluck('id')->toArray()
                : [];

            $detail->rams()->sync($availableRamIds);

            // Set default_ram_id if not provided but rams exist
            if (empty($validated['default_ram_id']) && count($availableRamIds)) {
                $detail->default_ram_id = \App\Models\Ram::whereIn('id', $availableRamIds)->orderBy('price')->value('id');
            }

            $detail->save();

            return response()->json([
                'status' => true,
                'message' => 'Laptop detail created successfully.',
                'data' => $detail->load([
                    'productModel',
                    'cpu',
                    'gpu',
                    'dedicatedGpu',
                    'defaultRam',
                    'defaultStorage',
                    'rams',
                    'storages',
                    'storageTypes',
                ]),
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->handleValidation($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

  
    /**
     * Get specific laptop detail
     *
     * @OA\Get(
     *     path="/api/laptop-details/{id}",
     *     summary="Get laptop specification by ID",
     *     tags={"Laptop Details"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Laptop detail ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/LaptopDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="LaptopDetail not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $detail = LaptopDetail::with([
                'productModel',
                'rams',
                'storages',
                'defaultRam',
                'defaultStorage',
                'cpu',
                'gpu',
                'dedicatedGpu',
                'storageTypes',
            ])->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $detail
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('LaptopDetail not found.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }



 /**
     * Update laptop specification
     *
     * @OA\Put(
     *     path="/api/laptop-details/{id}",
     *     summary="Update laptop configuration",
     *     tags={"Laptop Details"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Laptop detail ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_model_id", type="integer", example=2),
     *             @OA\Property(property="brand_id", type="integer", example=3),
     *             @OA\Property(property="cpu_id", type="integer", example=2),
     *             @OA\Property(property="gpu_id", type="integer", nullable=true, example=2),
     *             @OA\Property(property="dedicated_gpu_id", type="integer", nullable=true, example=2),
     *             @OA\Property(property="base_price", type="number", format="float", example=15000),
     *             @OA\Property(property="default_ram_id", type="integer", nullable=true, example=5),
     *             @OA\Property(property="default_storage_id", type="integer", nullable=true, example=3),
     *             @OA\Property(
     *                 property="ram_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={5,6,7}
     *             ),
     *             @OA\Property(
     *                 property="storage_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={2,3}
     *             ),
     *             @OA\Property(
     *                 property="storage_type_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1,2}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laptop detail updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LaptopDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="LaptopDetail not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */


public function update(Request $request, $id)
{
    try {
        $detail = LaptopDetail::findOrFail($id);

        $validated = $request->validate([
            'product_model_id' => 'sometimes|exists:product_models,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'cpu_id' => 'sometimes|exists:cpus,id',
            'gpu_id' => 'sometimes|nullable|exists:gpus,id',
            'dedicated_gpu_id' => 'sometimes|nullable|exists:dedicated_gpus,id',
            'base_price' => 'sometimes|numeric|min:0',
            'default_ram_id' => 'sometimes|nullable|exists:rams,id',
            'default_storage_id' => 'sometimes|nullable|exists:storages,id',
            'ram_ids' => 'sometimes|array',
            'ram_ids.*' => 'exists:rams,id',
            'storage_ids' => 'sometimes|array',
            'storage_ids.*' => 'exists:storages,id',
            'storage_type_ids' => 'sometimes|array',
            'storage_type_ids.*' => 'exists:storage_types,id',
        ]);

        // التعامل مع default_ram_id و default_storage_id إذا مش موجودين بس فيه ram_ids أو storage_ids
        if (!array_key_exists('default_ram_id', $validated) && $request->filled('ram_ids')) {
            $validated['default_ram_id'] = $request->ram_ids[0];
        }
        if (!array_key_exists('default_storage_id', $validated) && $request->filled('storage_ids')) {
            $validated['default_storage_id'] = $request->storage_ids[0];
        }

        $detail->update($validated);

        if ($request->has('ram_ids')) {
            $detail->rams()->sync($request->input('ram_ids', []));
        }
        if ($request->has('storage_ids')) {
            $detail->storages()->sync($request->input('storage_ids', []));
        }
        if ($request->has('storage_type_ids')) {
            $detail->storageTypes()->sync($request->input('storage_type_ids', []));
        }

        return response()->json([
            'status' => true,
            'message' => 'Laptop detail updated successfully.',
            'data' => $detail->load([
                'productModel',
                'cpu',
                'gpu',
                'dedicatedGpu',
                'defaultRam',
                'defaultStorage',
                'rams',
                'storages',
                'storageTypes',
            ]),
        ]);
    } catch (ModelNotFoundException $e) {
        return $this->notFound('LaptopDetail not found.');
    } catch (ValidationException $e) {
        return $this->handleValidation($e);
    } catch (\Exception $e) {
        return $this->handleException($e);
    }
}


   /**
     * Delete laptop specification
     *
     * @OA\Delete(
     *     path="/api/laptop-details/{id}",
     *     summary="Remove laptop configuration",
     *     tags={"Laptop Details"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Laptop detail ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Laptop detail deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="LaptopDetail not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $detail = LaptopDetail::findOrFail($id);
            $detail->delete();

            return response()->json([
                'status' => true,
                'message' => 'Laptop detail deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('LaptopDetail not found.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Handle general exceptions.
     *
     * @param \Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleException(\Exception $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Unexpected error occurred.',
            'error' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Handle validation exceptions.
     *
     * @param ValidationException $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleValidation(ValidationException $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors()
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Handle model not found exceptions.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFound(string $message)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], Response::HTTP_NOT_FOUND);
    }
}

/**
 * @OA\Schema(
 *     schema="LaptopDetail",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_model_id", type="integer", example=2),
 *     @OA\Property(property="brand_id", type="integer", example=3),
 *     @OA\Property(property="cpu_id", type="integer", example=2),
 *     @OA\Property(property="gpu_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="dedicated_gpu_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="base_price", type="number", format="float", example=15000),
 *     @OA\Property(property="default_ram_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="default_storage_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="ram_type_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="productModel",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="cpu",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="rams",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Ram")
 *     ),
 *     @OA\Property(
 *         property="storages",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Storage")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Ram",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="size", type="integer"),
 *     @OA\Property(property="price", type="number")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Storage",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="size", type="integer"),
 *     @OA\Property(property="price", type="number")
 * )
 */