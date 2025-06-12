<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController; 
use App\Http\Controllers\Auth\RegisterController;

// Public routes

Route::post('/register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',   [AuthController::class, 'logout']);
    Route::post('checkout', [CheckoutController::class, 'store']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order-items', OrderItemController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('admins', AdminController::class);
    Route::apiResource('cart-items', CartItemController::class);
    Route::apiResource('products', ProductController::class);
    // ... إضافة أي راوت آخر محمي
});

Route::middleware('auth:sanctum')->get('/test-token', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'user' => $request->user(),
        'token_valid' => true
    ]);
});