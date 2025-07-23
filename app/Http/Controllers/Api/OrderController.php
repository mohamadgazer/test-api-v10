<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * @queryParam per_page int Number of results per page. Defaults to 10.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'data' => Order::with('user')->paginate(request('per_page', 10))
        ]);
    }

    /**
     * Display the specified order.
     *
     * @urlParam id integer required The ID of the order.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $order = Order::with('user')->findOrFail($id);
            return response()->json(['data' => $order]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    /**
     * Store a newly created order.
     *
     * @bodyParam user_id integer required ID of the user. Example: 1
     * @bodyParam status string required Order status. Example: pending
     * @bodyParam total numeric required Total amount. Example: 1299.99
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'status' => 'required|string',
                'total' => 'required|numeric'
            ]);

            $order = Order::create($validated);

            return response()->json(['data' => $order], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }
    }

    /**
     * Update the specified order.
     *
     * @urlParam id integer required ID of the order.
     * @bodyParam status string optional New order status. Example: shipped
     * @bodyParam total numeric optional New total amount. Example: 1499.99
     * @bodyParam user_id integer optional New user ID. Example: 2
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            $validated = $request->validate([
                'status' => 'sometimes|string',
                'total' => 'sometimes|numeric',
                'user_id' => 'sometimes|exists:users,id'
            ]);

            $order->update($validated);

            return response()->json(['data' => $order]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified order.
     *
     * @urlParam id integer required ID of the order.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return response()->json(['message' => 'Order deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }
}
