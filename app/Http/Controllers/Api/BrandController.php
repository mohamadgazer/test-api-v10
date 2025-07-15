<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BrandController extends Controller
{
    public function index()
    {
        return Brand::all();
    }

    public function store(Request $request)
    {
        $request->validate([  'name' => 'required|string',
    'logo' => 'nullable|url']);
        $brand = Brand::create($request->only('name'));
        return response()->json($brand, 201);
    }

    public function show($id)
    {
        return Brand::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $request->validate([  'name' => 'required|string',
    'logo' => 'nullable|url']);
        $brand->update($request->only('name'));
        return response()->json($brand);
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
