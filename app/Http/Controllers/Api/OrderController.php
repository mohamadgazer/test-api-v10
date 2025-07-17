<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with('user')->paginate(request('per_page', 10)); 
    }

    public function show($id)
    {
        return Order::with('user')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string',
            'total' => 'required|numeric'
        ]);

        $order = Order::create($validated);
        return response()->json($order, 201);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|string',
            'total' => 'sometimes|numeric',
            'user_id' => 'sometimes|exists:users,id'
        ]);

        $order->update($validated);
        return $order;
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
