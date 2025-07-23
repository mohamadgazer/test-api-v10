<?php

namespace App\Http\Controllers\Api;

use App\Models\LaptopDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class LaptopDetailController extends Controller
{
    public function index()
    {
        try {
            $data = LaptopDetail::with([
                'productModel',
                'rams',
                'storages',
                'defaultRam',
                'defaultStorage'
            ])->paginate(request('per_page', 10));

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

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

            // Associate related models
            $storageTypeIds = $request->input('storage_type_ids', []);
            $detail->storageTypes()->sync($storageTypeIds);

            $availableStorageIds = \App\Models\Storage::whereIn('storage_type_id', $storageTypeIds)->pluck('id')->toArray();
            $detail->storages()->sync($availableStorageIds);

            if (!$validated['default_storage_id'] && count($availableStorageIds)) {
                $detail->default_storage_id = \App\Models\Storage::whereIn('id', $availableStorageIds)->orderBy('price')->value('id');
            }

            $ramTypeId = $validated['ram_type_id'] ?? null;
            $availableRamIds = $ramTypeId
                ? \App\Models\Ram::where('ram_type_id', $ramTypeId)->pluck('id')->toArray()
                : [];

            $detail->rams()->sync($availableRamIds);

            if (!$validated['default_ram_id'] && count($availableRamIds)) {
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
                ])
            ], 201);
        } catch (ValidationException $e) {
            return $this->handleValidation($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $detail = LaptopDetail::findOrFail($id);

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
                'ram_ids' => 'nullable|array',
                'ram_ids.*' => 'exists:rams,id',
                'storage_ids' => 'nullable|array',
                'storage_ids.*' => 'exists:storages,id',
                'storage_type_ids' => 'nullable|array',
                'storage_type_ids.*' => 'exists:storage_types,id',
            ]);

            if (empty($validated['default_ram_id']) && !empty($request->ram_ids)) {
                $validated['default_ram_id'] = $request->ram_ids[0];
            }
            if (empty($validated['default_storage_id']) && !empty($request->storage_ids)) {
                $validated['default_storage_id'] = $request->storage_ids[0];
            }

            $detail->update($validated);

            $detail->rams()->sync($request->input('ram_ids', []));
            $detail->storages()->sync($request->input('storage_ids', []));
            $detail->storageTypes()->sync($request->input('storage_type_ids', []));

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
                ])
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('LaptopDetail not found.');
        } catch (ValidationException $e) {
            return $this->handleValidation($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

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

    public function show($id)
    {
        try {
            $detail = LaptopDetail::with([
                'productModel',
                'rams',
                'storages',
                'defaultRam',
                'defaultStorage'
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

    // ✅ هندلة الأخطاء العامة
    protected function handleException(\Exception $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Unexpected error occurred.',
            'error' => $e->getMessage()
        ], 500);
    }

    // ✅ هندلة التحقق
    protected function handleValidation(ValidationException $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors()
        ], 422);
    }

    // ✅ هندلة Not Found
    protected function notFound($message)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], 404);
    }
}
