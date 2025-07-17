<?php

namespace App\Http\Controllers\Api;

use App\Models\StorageType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StorageTypeController extends Controller
{
    public function index()
    {
        return StorageType::paginate(request('per_page', 10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:StorageType,name',
        ]);

        return RamType::create($validated);
    }

    public function show($id)
    {
        return StorageType::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $ramType = StorageType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:StorageType,name,' . $id,
        ]);

        $ramType->update($validated);

        return $ramType;
    }

    public function destroy($id)
    {
        $ramType = StorageType::findOrFail($id);
        $ramType->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
