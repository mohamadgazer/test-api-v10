<?php

namespace App\Http\Controllers\Api;

use App\Models\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StorageController extends Controller
{
    public function index()
    {
        return Storage::paginate(request('per_page', 10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'size_gb' => 'required|numeric|min:1',
        ]);
        
        $Storage = Storage::create($request->only('name'));
        return response()->json($Storage, 201);
    }

    public function show($id)
    {
        return Storage::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $Storage = Storage::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $Storage->update($request->only('name'));
        return response()->json($Storage);
    }

    public function destroy($id)
    {
        $Storage = Storage::findOrFail($id);
        $Storage->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
