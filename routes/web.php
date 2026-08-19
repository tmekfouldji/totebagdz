<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index']);
Route::view('/admin', 'admin')->name('admin');
Route::redirect('/admin.html', '/admin');

Route::prefix('api')->group(function () {
    Route::get('/product', [StoreController::class, 'product']);
    Route::get('/shipping/wilayas', [ShippingController::class, 'wilayas']);
    Route::get('/shipping/communes', [ShippingController::class, 'communes']);
    Route::get('/shipping/fees', [ShippingController::class, 'fees']);
    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:20,1');

    Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:8,1');
    Route::middleware('auth')->prefix('admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/dashboard', DashboardController::class);
        Route::get('/orders/export', [AdminOrderController::class, 'export']);
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}', [AdminOrderController::class, 'update']);
        Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy']);
        Route::post('/orders/{order}/dispatch', [AdminOrderController::class, 'dispatch']);
        Route::get('/product', [AdminProductController::class, 'show']);
        Route::patch('/product', [AdminProductController::class, 'update']);
    });
});
