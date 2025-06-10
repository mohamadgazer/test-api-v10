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
use App\Http\Controllers\Api\CheckoutController; // أضف هذا الاستيراد

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Checkout route - تأكد من وجود هذا المسار
    Route::post('/checkout', [CheckoutController::class, 'store']); // أصلح هذا السطر

    // Other protected routes
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order-items', OrderItemController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('admins', AdminController::class);
    Route::apiResource('cart-items', CartItemController::class);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/test-token', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'user' => $request->user(),
        'token_valid' => true
    ]);
});