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
            'product_model_id' => 'required|exists:product_models,id', // ✅ صح
            'brand_id' => 'required|exists:brands,id',
            'cpu_id' => 'required|exists:cpus,id',
            'gpu_id' => 'nullable|exists:gpus,id',
            'dedicated_gpu_id' => 'nullable|exists:dedicated_gpus,id',
            'base_price' => 'required|numeric|min:0',
            'default_ram_id' => 'nullable|exists:rams,id',
            'default_storage_id' => 'nullable|exists:storages,id',
        ]);
        
        $detail = LaptopDetail::create($validated);
        
        return $detail->load([
            'productModel',
            'cpu',
            'gpu',
            'dedicatedGpu',
            'defaultRam',
            'defaultStorage',
            'rams',
            'storages'
        ]);
        
    }

    public function update(Request $request, $id)
    {
        $detail = LaptopDetail::findOrFail($id);
        
        $validated = $request->validate([
            'product_model_id' => 'sometimes|exists:product_models,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'cpu_id' => 'sometimes|exists:cpus,id',
            'gpu_id' => 'nullable|exists:gpus,id',
            'dedicated_gpu_id' => 'nullable|exists:dedicated_gpus,id',
            'base_price' => 'sometimes|numeric|min:0',
            'default_ram_id' => 'nullable|exists:rams,id',
            'default_storage_id' => 'nullable|exists:storages,id',
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
            'storages'
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
