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
