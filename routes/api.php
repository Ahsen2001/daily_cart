<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\CartController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\RiderController;
use App\Http\Controllers\Api\v1\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        // Customer Cart
        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart', [CartController::class, 'add']);
        Route::patch('/cart-items/{item}', [CartController::class, 'update']);
        Route::delete('/cart-items/{item}', [CartController::class, 'remove']);
        Route::delete('/cart/clear', [CartController::class, 'clear']);

        // Customer Checkout & Orders
        Route::post('/checkout/quote', [OrderController::class, 'quote']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        // Rider endpoints
        Route::prefix('rider')->middleware('role:Rider')->group(function () {
            Route::get('/deliveries', [RiderController::class, 'index']);
            Route::get('/deliveries/{delivery}', [RiderController::class, 'show']);
            Route::patch('/deliveries/{delivery}/status', [RiderController::class, 'updateStatus']);
            Route::post('/location', [RiderController::class, 'location']);
        });

        // Vendor endpoints
        Route::prefix('vendor')->middleware('role:Vendor')->group(function () {
            Route::get('/overview', [VendorController::class, 'overview']);
            Route::get('/orders', [VendorController::class, 'orders']);
            Route::get('/wallet', [VendorController::class, 'wallet']);
        });
    });
});
