<?php

namespace App\Http\Controllers\Api;

use App\Models\LaptopDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Laptop Details
 *
 * APIs for managing laptop details such as model, brand, CPU, GPU, RAM, storage, and pricing.
 */
class LaptopDetailController extends Controller
{
    /**
     * List all laptop details (paginated).
     *
     * Retrieve a paginated list of laptop details with related models.
     *
     * @queryParam per_page int Number of results per page. Defaults to 10. Example: 15
     *
     * @response 200 {
     *   "status": true,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "product_model_id": 2,
     *         "brand_id": 3,
     *         "cpu_id": 2,
     *         "gpu_id": 2,
     *         "dedicated_gpu_id": 2,
     *         "base_price": 15000,
     *         "default_ram_id": 1,
     *         "default_storage_id": 1,
     *         "ram_type_id": 2,
     *         "created_at": "...",
     *         "updated_at": "...",
     *         "productModel": { ... },
     *         "cpu": { ... },
     *         "gpu": { ... },
     *         "dedicatedGpu": { ... },
     *         "defaultRam": { ... },
     *         "defaultStorage": { ... },
     *         "rams": [ ... ],
     *         "storages": [ ... ],
     *         "storageTypes": [ ... ]
     *       }
     *     ],
     *     ...
     *   }
     * }
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
     * Create a new laptop detail.
     *
     * @bodyParam product_model_id int required The product model ID. Must exist in product_models table. Example: 2
     * @bodyParam brand_id int required The brand ID. Must exist in brands table. Example: 3
     * @bodyParam cpu_id int required The CPU ID. Must exist in cpus table. Example: 2
     * @bodyParam gpu_id int nullable The integrated GPU ID. Must exist in gpus table. Example: 2
     * @bodyParam dedicated_gpu_id int nullable The dedicated GPU ID. Must exist in dedicated_gpus table. Example: 2
     * @bodyParam base_price numeric required Base price without RAM/storage. Example: 15000
     * @bodyParam default_ram_id int nullable Default RAM ID. Must exist in rams table.
     * @bodyParam default_storage_id int nullable Default storage ID. Must exist in storages table.
     * @bodyParam ram_type_id int nullable RAM type ID. Must exist in ram_types table.
     * @bodyParam storage_type_ids array<int> Nullable array of storage type IDs. Each must exist in storage_types table.
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Laptop detail created successfully.",
     *   "data": { ... }
     * }
     *
     * @response 422 {
     *   "status": false,
     *   "message": "Validation failed.",
     *   "errors": {
     *     "product_model_id": ["The product model id field is required."],
     *     ...
     *   }
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Unexpected error occurred.",
     *   "error": "..."
     * }
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
     * Show a specific laptop detail by ID.
     *
     * @urlParam id int required The ID of the laptop detail. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "data": { ... }
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "LaptopDetail not found."
     * }
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
 * Update an existing laptop detail (partial or full update)
 *
 * Allows updating one or more fields of a laptop detail.
 * Fields not provided will remain unchanged.
 *
 * @urlParam id integer required The ID of the laptop detail to update. Example: 1
 *
 * @bodyParam product_model_id integer The ID of the product model. Example: 2
 * @bodyParam brand_id integer The ID of the brand. Example: 3
 * @bodyParam cpu_id integer The ID of the CPU. Example: 2
 * @bodyParam gpu_id integer Nullable. The ID of the integrated GPU. Example: 2
 * @bodyParam dedicated_gpu_id integer Nullable. The ID of the dedicated GPU. Example: 2
 * @bodyParam base_price numeric The base price of the laptop detail. Example: 15000
 * @bodyParam default_ram_id integer Nullable. The default RAM ID. Example: 5
 * @bodyParam default_storage_id integer Nullable. The default Storage ID. Example: 3
 * @bodyParam ram_ids array Nullable. List of RAM IDs associated. Example: [5,6,7]
 * @bodyParam storage_ids array Nullable. List of Storage IDs associated. Example: [2,3]
 * @bodyParam storage_type_ids array Nullable. List of Storage Type IDs associated. Example: [1,2]
 *
 * @response 200 {
 *   "status": true,
 *   "message": "Laptop detail updated successfully.",
 *   "data": {
 *     "id": 1,
 *     "product_model_id": 2,
 *     "brand_id": 3,
 *     "cpu_id": 2,
 *     "gpu_id": 2,
 *     "dedicated_gpu_id": 2,
 *     "base_price": 15000,
 *     "default_ram_id": 5,
 *     "default_storage_id": 3,
 *     "rams": [...],
 *     "storages": [...],
 *     "storageTypes": [...],
 *     ...
 *   }
 * }
 *
 * @response 404 {
 *   "status": false,
 *   "message": "LaptopDetail not found."
 * }
 *
 * @response 422 {
 *   "status": false,
 *   "message": "Validation failed.",
 *   "errors": {
 *     "cpu_id": ["The cpu id field is required."],
 *     ...
 *   }
 * }
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
     * Delete a laptop detail by ID.
     *
     * @urlParam id int required The ID of the laptop detail. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Laptop detail deleted successfully."
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "LaptopDetail not found."
     * }
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
