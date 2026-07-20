<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CartController;
use App\Http\Controllers\Api\v1\DeliveryPricingController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\PasswordRecoveryController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\RiderController;
use App\Http\Controllers\Api\v1\VendorController;
use App\Http\Controllers\Api\v1\VerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:api-register');
    Route::post('/register/customer', [AuthController::class, 'registerCustomer'])->middleware('throttle:api-register');
    Route::post('/register/vendor', [AuthController::class, 'registerVendor'])->middleware('throttle:api-register');
    Route::post('/register/rider', [AuthController::class, 'registerRider'])->middleware('throttle:api-register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');
    Route::post('/password/forgot', [PasswordRecoveryController::class, 'forgot'])->middleware('throttle:api-otp');
    Route::post('/password/reset', [PasswordRecoveryController::class, 'reset'])->middleware('throttle:api-otp');

    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/delivery/zones', [DeliveryPricingController::class, 'zones']);
    Route::get('/delivery/promotions', [DeliveryPricingController::class, 'promotions']);
    Route::post('/delivery/estimate', [DeliveryPricingController::class, 'estimate']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('ability:auth');
        Route::get('/profile', [AuthController::class, 'profile'])->middleware('ability:profile');

        Route::middleware(['ability:verification', 'throttle:api-otp'])->group(function () {
            Route::post('/email/verification-otp', [VerificationController::class, 'sendEmail']);
            Route::post('/email/verification-otp/verify', [VerificationController::class, 'verifyEmail']);
            Route::post('/phone/verification-otp', [VerificationController::class, 'sendPhone']);
            Route::post('/phone/verification-otp/verify', [VerificationController::class, 'verifyPhone']);
        });

        Route::middleware(['ability:customer', 'verified', 'phone.verified', 'role:Customer'])->group(function () {
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
        });

        // Rider endpoints
        Route::prefix('rider')->middleware(['ability:rider', 'verified', 'phone.verified', 'role:Rider', 'rider.approved'])->group(function () {
            Route::get('/deliveries', [RiderController::class, 'index']);
            Route::get('/deliveries/{delivery}', [RiderController::class, 'show']);
            Route::patch('/deliveries/{delivery}/status', [RiderController::class, 'updateStatus']);
            Route::post('/location', [RiderController::class, 'location']);
        });

        // Vendor endpoints
        Route::prefix('vendor')->middleware(['ability:vendor', 'verified', 'phone.verified', 'role:Vendor', 'vendor.approved'])->group(function () {
            Route::get('/overview', [VendorController::class, 'overview']);
            Route::get('/orders', [VendorController::class, 'orders']);
            Route::get('/wallet', [VendorController::class, 'wallet']);
        });
    });
});
