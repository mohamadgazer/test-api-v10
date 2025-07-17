<?php

namespace App\Http\Controllers\Api;

use App\Models\DedicatedGpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DedicatedGpuController extends Controller
{
public function index()
{
    return DedicatedGpu::paginate(request('per_page', 10));
}

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $DedicatedGpu = DedicatedGpu::create($request->only('name'));
        return response()->json($DedicatedGpu, 201);
    }

    public function show($id)
    {
        return DedicatedGpu::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $DedicatedGpu = DedicatedGpu::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $DedicatedGpu->update($request->only('name'));
        return response()->json($DedicatedGpu);
    }

    public function destroy($id)
    {
        $DedicatedGpu = DedicatedGpu::findOrFail($id);
        $DedicatedGpu->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
