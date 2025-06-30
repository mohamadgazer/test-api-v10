<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CheckoutController;

// Laptop details
use App\Http\Controllers\Api\ProductModelController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CPUController;
use App\Http\Controllers\Api\GPUController;
use App\Http\Controllers\Api\DedicatedGPUController;
use App\Http\Controllers\Api\RAMController;
use App\Http\Controllers\Api\RAMTypeController;
use App\Http\Controllers\Api\StorageTypeController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\LaptopDetailController;

// ✅ Public routes (no auth needed)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// ✅ Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order-items', OrderItemController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('cart-items', CartItemController::class);

    // Laptop-related APIs
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('cpus', CPUController::class);
    Route::apiResource('gpus', GPUController::class);
    Route::apiResource('dedicated-gpus', DedicatedGPUController::class);
    Route::apiResource('rams', RAMController::class);
    Route::apiResource('ram-types', RAMTypeController::class);
    Route::apiResource('storages', StorageController::class);
    Route::apiResource('storage-types', StorageTypeController::class);
    Route::apiResource('product-models', ProductModelController::class);
    Route::apiResource('product-images', ProductImageController::class);
    Route::apiResource('laptop-details', LaptopDetailController::class);
});

// ✅ Admin-only routes (protected by is_admin middleware)
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('products', ProductController::class);
});

// ✅ Token test route (debug only)
Route::middleware('auth:sanctum')->get('/test-token', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'user' => $request->user(),
        'token_valid' => true
    ]);
});


