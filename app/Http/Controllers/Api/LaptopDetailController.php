<?php

namespace App\Http\Controllers\Api;

use App\Models\LaptopDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LaptopDetailController extends Controller
{
    public function index()
    {
        return LaptopDetail::with(['productModel', 'rams', 'storages', 'defaultRam', 'defaultStorage'])->get();

    }

public function store(Request $request)
{
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

    // 1. إنشاء الجهاز
    $detail = LaptopDetail::create($validated);

    // 2. ربط أنواع التخزين
    $storageTypeIds = $request->input('storage_type_ids', []);
    $detail->storageTypes()->sync($storageTypeIds);

    // 3. استخراج وحدات التخزين المناسبة حسب النوع
    $availableStorageIds = \App\Models\Storage::whereIn('storage_type_id', $storageTypeIds)->pluck('id')->toArray();
    $detail->storages()->sync($availableStorageIds);

    // 4. اختيار أرخص وحدة كافتراضية (لو مش متحددة)
    if (empty($validated['default_storage_id']) && count($availableStorageIds)) {
        $cheapestStorageId = \App\Models\Storage::whereIn('id', $availableStorageIds)->orderBy('price')->value('id');
        $detail->default_storage_id = $cheapestStorageId;
    }

    // 5. استخراج الرامات حسب نوع الرام
    $ramTypeId = $validated['ram_type_id'] ?? null;
    $availableRamIds = $ramTypeId
        ? \App\Models\Ram::where('ram_type_id', $ramTypeId)->pluck('id')->toArray()
        : [];

    $detail->rams()->sync($availableRamIds);

    // 6. اختيار أرخص رام كافتراضية
    if (empty($validated['default_ram_id']) && count($availableRamIds)) {
        $cheapestRamId = \App\Models\Ram::whereIn('id', $availableRamIds)->orderBy('price')->value('id');
        $detail->default_ram_id = $cheapestRamId;
    }

    // 7. حفظ التغييرات النهائية
    $detail->save();

    // 8. إرجاع التفاصيل كاملة
    return $detail->load([
        'productModel',
        'cpu',
        'gpu',
        'dedicatedGpu',
        'defaultRam',
        'defaultStorage',
        'rams',
        'storages',
        'storageTypes',
    ]);
}

    

    public function update(Request $request, $id)
    {
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
        
        // افتراض default ram/storage لو مش متحدد
if (empty($validated['default_ram_id']) && !empty($request->ram_ids)) {
    $validated['default_ram_id'] = $request->ram_ids[0];
}
if (empty($validated['default_storage_id']) && !empty($request->storage_ids)) {
    $validated['default_storage_id'] = $request->storage_ids[0];
}

$detail = LaptopDetail::create($validated);

// ربط الرامات والتخزين وأنواع التخزين
$detail->rams()->sync($request->input('ram_ids', []));
$detail->storages()->sync($request->input('storage_ids', []));
$detail->storageType()->sync($request->input('storage_type_ids', [])); // 🔥 الجديد

return $detail->load([
    'productModel',
    'cpu',
    'gpu',
    'dedicatedGpu',
    'defaultRam',
    'defaultStorage',
    'rams',
    'storages',
    'storageTypes', // ✅
]);

        
        $detail->update($validated);
        
        return $detail->load([
            'productModel',
            'cpu',
            'gpu',
            'dedicatedGpu',
            'defaultRam',
            'defaultStorage',
            'rams',
            'storages',
            'storageTypes',
        ]);
        
    }

    public function destroy($id)
    {
        $detail = LaptopDetail::findOrFail($id);
        $detail->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function show($id)
{
    $detail = LaptopDetail::with([
        'productModel',
        'rams',
        'storages',
        'defaultRam',
        'defaultStorage'
    ])->findOrFail($id);

    return response()->json($detail);
}

}
