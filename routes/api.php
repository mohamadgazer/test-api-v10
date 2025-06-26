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

//labtop details
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

// Public routes

Route::post('/register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
// Route::get('/products', [ProductController::class, 'index']);
// Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
// Route::apiResource('products', ProductController::class);
// Route::post('/products', [ProductController::class, 'store']);
Route::apiResource('products', ProductController::class);

// labtop details
Route::apiResource('brands', BrandController::class);
Route::apiResource('cpus', CpuController::class);
Route::apiResource('gpus', GpuController::class);
Route::apiResource('dedicated-gpus', DedicatedGpuController::class);
Route::apiResource('rams', RamController::class);
Route::apiResource('storages', StorageController::class);
Route::apiResource('product-models', ProductModelController::class);
Route::apiResource('ram-types', RamTypeController::class);
Route::apiResource('storage-types', StorageTypeController::class);
Route::apiResource('product-images', ProductImageController::class);
Route::apiResource('laptop-details', LaptopDetailController::class);



Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',   [AuthController::class, 'logout']);
    Route::post('checkout', [CheckoutController::class, 'store']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order-items', OrderItemController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('admins', AdminController::class);
    Route::apiResource('cart-items', CartItemController::class);
    // Route::apiResource('products', ProductController::class);
    // ... إضافة أي راوت آخر محمي
});

Route::middleware('auth:sanctum')->get('/test-token', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'user' => $request->user(),
        'token_valid' => true
    ]);
});