<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OrderItemController extends Controller
{
    /**
     * Display a paginated list of order items.
     *
     * @queryParam per_page int Number of results per page. Default: 10.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $items = OrderItem::with(['order', 'product'])->paginate(request('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Store a newly created order item.
     *
     * @bodyParam order_id int required The ID of the order. Example: 1
     * @bodyParam product_id int required The ID of the product. Example: 5
     * @bodyParam quantity int required Quantity ordered. Minimum: 1. Example: 2
     * @bodyParam price float required Price per unit. Minimum: 0. Example: 99.99
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
            ]);

            $item = OrderItem::create($validated);

            return response()->json([
                'success' => true,
                'data' => $item
            ], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display a specific order item by ID.
     *
     * @urlParam id int required The ID of the order item. Example: 3
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $item = OrderItem::with(['order', 'product'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $item
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found.'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update an existing order item.
     *
     * @urlParam id int required The ID of the order item.
     * @bodyParam order_id int The ID of the order.
     * @bodyParam product_id int The ID of the product.
     * @bodyParam quantity int Quantity ordered. Minimum: 1.
     * @bodyParam price float Price per unit. Minimum: 0.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $item = OrderItem::findOrFail($id);

            $validated = $request->validate([
                'order_id' => 'sometimes|exists:orders,id',
                'product_id' => 'sometimes|exists:products,id',
                'quantity' => 'sometimes|integer|min:1',
                'price' => 'sometimes|numeric|min:0',
            ]);

            $item->update($validated);

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Delete an order item.
     *
     * @urlParam id int required The ID of the order item.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $item = OrderItem::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order item deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found.'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
