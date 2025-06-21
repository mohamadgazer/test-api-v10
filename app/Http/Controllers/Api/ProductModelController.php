<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductModelController extends Controller
{
    public function index()
    {
        return ProductModel::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'ProductModel_id' => 'required|exists:ProductModels,id',
        ]);
        
        $ProductModel = ProductModel::create($request->only('name'));
        return response()->json($ProductModel, 201);
    }

    public function show($id)
    {
        return ProductModel::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $ProductModel = ProductModel::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $ProductModel->update($request->only('name'));
        return response()->json($ProductModel);
    }

    public function destroy($id)
    {
        $ProductModel = ProductModel::findOrFail($id);
        $ProductModel->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
