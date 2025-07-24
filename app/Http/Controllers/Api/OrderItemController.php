<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @group Order Items Management
 *
 * APIs for managing order items (Admins and Order Owner only)
 */
class OrderItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/order-items",
     *     summary="Get paginated list of order items",
     *     tags={"Order Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/OrderItemPaginated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = Auth::user();
        
        // For admins, show all order items
        if ($user->is_admin) {
            $items = OrderItem::with(['order', 'product'])->paginate(request('per_page', 10));
        } 
        // For regular users, show only their own order items
        else {
            $items = OrderItem::whereHas('order', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['order', 'product'])
            ->paginate(request('per_page', 10));
        }

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * @OA\Post(
     *     path="/order-items",
     *     summary="Create a new order item (Admin only)",
     *     tags={"Order Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","product_id","quantity","price"},
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=5),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="price", type="number", format="float", example=99.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order item created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/OrderItem"),
     *             @OA\Property(property="message", type="string", example="Order item created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can create order items'
                ], Response::HTTP_FORBIDDEN);
            }

            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
            ]);

            $item = OrderItem::create($validated);

            return response()->json([
                'success' => true,
                'data' => $item->load(['order', 'product']),
                'message' => 'Order item created successfully'
            ], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/order-items/{id}",
     *     summary="Get specific order item by ID",
     *     tags={"Order Items"},
    *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of order item to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/OrderItemWithRelations")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You don't have permission to access this order item")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order item not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $item = OrderItem::with(['order', 'product'])->findOrFail($id);

            // Check if user is admin or order owner
            if (!$user->is_admin && $item->order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You don\'t have permission to access this order item'
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'success' => true,
                'data' => $item
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/order-items/{id}",
     *     summary="Update an order item (Admin only)",
     *     tags={"Order Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of order item to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", example=1, nullable=true),
     *             @OA\Property(property="product_id", type="integer", example=5, nullable=true),
     *             @OA\Property(property="quantity", type="integer", example=3, nullable=true),
     *             @OA\Property(property="price", type="number", format="float", example=89.99, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order item updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/OrderItem"),
     *             @OA\Property(property="message", type="string", example="Order item updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only admins can update order items")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order item not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can update order items'
                ], Response::HTTP_FORBIDDEN);
            }

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
                'data' => $item->load(['order', 'product']),
                'message' => 'Order item updated successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/order-items/{id}",
     *     summary="Delete an order item (Admin only)",
     *     tags={"Order Items"},
    *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of order item to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order item deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order item deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only admins can delete order items")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order item not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can delete order items'
                ], Response::HTTP_FORBIDDEN);
            }

            $item = OrderItem::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order item deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}