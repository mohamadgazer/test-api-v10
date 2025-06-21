<?php

namespace App\Http\Controllers\Api;

use App\Models\Ram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RamController extends Controller
{
    public function index()
    {
        return Ram::with('ramType')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'size_gb' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'ram_type_id' => 'required|exists:ram_types,id',
        ]);
    
        $ram = Ram::create($validated);
    
        return response()->json($ram->load('ramType'), 201);
    }
    
    public function update(Request $request, $id)
    {
        $ram = Ram::findOrFail($id);
    
        $validated = $request->validate([
            'size_gb' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'ram_type_id' => 'required|exists:ram_types,id',
        ]);
    
        $ram->update($validated);
    
        return response()->json($ram->load('ramType'));
    }
    

    public function destroy($id)
    {
        $ram = Ram::findOrFail($id);
        $ram->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
