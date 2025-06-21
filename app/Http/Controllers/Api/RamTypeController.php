<?php

namespace App\Http\Controllers\Api;

use App\Models\RamType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RamTypeController extends Controller
{
    public function index()
    {
        return RamType::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:ram_types,name',
        ]);

        return RamType::create($validated);
    }

    public function show($id)
    {
        return RamType::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $ramType = RamType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:ram_types,name,' . $id,
        ]);

        $ramType->update($validated);

        return $ramType;
    }

    public function destroy($id)
    {
        $ramType = RamType::findOrFail($id);
        $ramType->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
