<?php

namespace App\Http\Controllers\Api;

use App\Models\Cpu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CpuController extends Controller
{
public function index()
{
    return Cpu::paginate(request('per_page', 10));
}


    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $Cpu = Cpu::create($request->only('name'));
        return response()->json($Cpu, 201);
    }

    public function show($id)
    {
        return Cpu::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $Cpu = Cpu::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $Cpu->update($request->only('name'));
        return response()->json($Cpu);
    }

    public function destroy($id)
    {
        $Cpu = Cpu::findOrFail($id);
        $Cpu->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
