<?php

namespace App\Http\Controllers\Api;

use App\Models\Gpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GpuController extends Controller
{
public function index()
{
    return Gpu::paginate(request('per_page', 10));
}


    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $Gpu = Gpu::create($request->only('name'));
        return response()->json($Gpu, 201);
    }

    public function show($id)
    {
        return Gpu::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $Gpu = Gpu::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $Gpu->update($request->only('name'));
        return response()->json($Gpu);
    }

    public function destroy($id)
    {
        $Gpu = Gpu::findOrFail($id);
        $Gpu->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
