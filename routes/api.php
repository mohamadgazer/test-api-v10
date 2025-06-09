<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

Route::apiResource('products', ProductController::class);


use App\Http\Controllers\Api\CategoryController;
Route::apiResource('categories', CategoryController::class);

use App\Http\Controllers\Api\OrderController;
Route::apiResource('orders', OrderController::class);

use App\Http\Controllers\Api\OrderItemController;
Route::apiResource('order-items', OrderItemController::class);

use App\Http\Controllers\Api\UserController;
Route::middleware('auth:sanctum')->apiResource('users', UserController::class);


use App\Http\Controllers\Api\AdminController;
Route::apiResource('admins', AdminController::class);

use App\Http\Controllers\Api\CartItemController;
Route::apiResource('cart-items', CartItemController::class);


use App\Http\Controllers\Auth\RegisterController;

Route::post('register', [RegisterController::class, 'register']);


// // لا تحتاج تعديلات الآن، فقط تأكد إنك لما تبدأ تحمي routes لاحقًا تستخدم:
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
