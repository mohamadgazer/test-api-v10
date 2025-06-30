<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ProductController,
    CategoryController,
    OrderController,
    OrderItemController,
    UserController,
    CartItemController,
    AuthController,
    CheckoutController,
    ProductModelController,
    BrandController,
    CPUController,
    GPUController,
    DedicatedGPUController,
    RAMController,
    RAMTypeController,
    StorageTypeController,
    StorageController,
    ProductImageController,
    LaptopDetailController
};

// ✅ Routes عامة (بدون تسجيل دخول)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// ✅ Routes لمستخدم مسجل الدخول (بالتوكن)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout',   [AuthController::class, 'logout']);
    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::apiResource('cart-items', CartItemController::class);
    Route::get('orders', [OrderController::class, 'index']); // عرض الطلبات فقط

    // ❌ لا يسمح للمستخدم العادي بالتعديل أو الحذف
});



// ✅ Routes خاصة بالمشرف (Admin فقط)
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    // التحكم الكامل بالمنتجات
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);

    Route::apiResource('users', UserController::class); // إدارة المستخدمين
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('orders', OrderController::class);         // كل الطلبات
    Route::apiResource('order-items', OrderItemController::class);
    
    // إدارة مكونات اللابتوب
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('cpus', CPUController::class);
    Route::apiResource('gpus', GPUController::class);
    Route::apiResource('dedicated-gpus', DedicatedGPUController::class);
    Route::apiResource('rams', RAMController::class);
    Route::apiResource('storages', StorageController::class);
    Route::apiResource('ram-types', RAMTypeController::class);
    Route::apiResource('storage-types', StorageTypeController::class);
    Route::apiResource('product-models', ProductModelController::class);
    Route::apiResource('product-images', ProductImageController::class);
    Route::apiResource('laptop-details', LaptopDetailController::class);


    Route::get('/admins', [UserController::class, 'admins']);

});

// ✅ فحص صلاحية التوكن فقط (اختياري)
Route::middleware('auth:sanctum')->get('/test-token', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'user' => $request->user(),
        'token_valid' => true
    ]);
});
