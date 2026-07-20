<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\AddressController;
use App\Http\Controllers\Api\v1\CartController;
use App\Http\Controllers\Api\v1\CustomerCommerceController;
use App\Http\Controllers\Api\v1\CustomerAccountController;
use App\Http\Controllers\Api\v1\CustomerExtendedCommerceController;
use App\Http\Controllers\Api\v1\DeliveryPricingController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\PasswordRecoveryController;
use App\Http\Controllers\Api\v1\PayHereMobileController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\RiderController;
use App\Http\Controllers\Api\v1\VendorController;
use App\Http\Controllers\Api\v1\VendorBusinessController;
use App\Http\Controllers\Api\v1\VendorCatalogController;
use App\Http\Controllers\Api\v1\VerificationController;
use App\Http\Controllers\Api\v1\WishlistController;
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
    Route::get('/payments/{payment}/payhere/form', [PayHereMobileController::class, 'form'])
        ->middleware('signed')->name('api.v1.payhere.form');
    Route::get('/payments/{payment}/payhere/return', [PayHereMobileController::class, 'return'])
        ->middleware('signed')->name('api.v1.payhere.return');
    Route::get('/payments/{payment}/payhere/cancel', [PayHereMobileController::class, 'cancel'])
        ->middleware('signed')->name('api.v1.payhere.cancel');

    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/delivery/zones', [DeliveryPricingController::class, 'zones']);
    Route::get('/delivery/promotions', [DeliveryPricingController::class, 'promotions']);
    Route::post('/delivery/estimate', [DeliveryPricingController::class, 'estimate']);
    Route::get('/policies', [CustomerExtendedCommerceController::class, 'policies']);

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

            Route::get('/wishlist', [WishlistController::class, 'index']);
            Route::post('/wishlist', [WishlistController::class, 'store']);
            Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);
            Route::post('/wishlist/{product}/move-to-cart', [WishlistController::class, 'moveToCart']);

            Route::get('/addresses', [AddressController::class, 'index']);
            Route::post('/addresses', [AddressController::class, 'store']);
            Route::patch('/addresses/{address}', [AddressController::class, 'update']);
            Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
            Route::patch('/addresses/{address}/default', [AddressController::class, 'makeDefault']);

            // Customer Checkout & Orders
            Route::post('/checkout/quote', [OrderController::class, 'quote']);
            Route::post('/orders', [OrderController::class, 'store']);
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{order}', [OrderController::class, 'show']);
            Route::get('/orders/{order}/status', [CustomerCommerceController::class, 'orderStatus']);
            Route::patch('/orders/{order}/cancel', [CustomerCommerceController::class, 'cancelOrder']);

            Route::get('/coupons/available', [CustomerCommerceController::class, 'coupons']);
            Route::post('/coupons/apply', [CustomerCommerceController::class, 'validateCoupon']);
            Route::post('/coupons/validate', [CustomerCommerceController::class, 'validateCoupon']);
            Route::delete('/coupons/remove', [CustomerCommerceController::class, 'removeCoupon']);
            Route::get('/loyalty/balance', [CustomerCommerceController::class, 'loyaltyBalance']);
            Route::get('/loyalty/history', [CustomerCommerceController::class, 'loyaltyHistory']);

            Route::get('/wallet', [CustomerExtendedCommerceController::class, 'wallet']);
            Route::get('/wallet/transactions', [CustomerExtendedCommerceController::class, 'walletTransactions']);
            Route::get('/refunds', [CustomerExtendedCommerceController::class, 'refunds']);
            Route::get('/refunds/{refund}', [CustomerExtendedCommerceController::class, 'refund']);
            Route::post('/orders/{order}/refunds', [CustomerExtendedCommerceController::class, 'requestRefund']);

            Route::get('/subscriptions', [CustomerExtendedCommerceController::class, 'subscriptions']);
            Route::post('/subscriptions', [CustomerExtendedCommerceController::class, 'createSubscription']);
            Route::get('/subscriptions/upcoming', [CustomerExtendedCommerceController::class, 'upcomingSubscriptions']);
            Route::get('/subscriptions/{subscription}', [CustomerExtendedCommerceController::class, 'subscription']);
            Route::patch('/subscriptions/{subscription}', [CustomerExtendedCommerceController::class, 'updateSubscription']);
            Route::patch('/subscriptions/{subscription}/pause', [CustomerExtendedCommerceController::class, 'pauseSubscription']);
            Route::patch('/subscriptions/{subscription}/resume', [CustomerExtendedCommerceController::class, 'resumeSubscription']);
            Route::patch('/subscriptions/{subscription}/cancel', [CustomerExtendedCommerceController::class, 'cancelSubscription']);
            Route::get('/scheduled-orders', [CustomerExtendedCommerceController::class, 'scheduledOrders']);
            Route::patch('/scheduled-orders/{order}/cancel', [CustomerExtendedCommerceController::class, 'cancelScheduledOrder']);

            Route::get('/products/{product}/reviews', [CustomerCommerceController::class, 'productReviews']);
            Route::get('/reviews/my', [CustomerCommerceController::class, 'myReviews']);
            Route::post('/reviews', [CustomerCommerceController::class, 'storeReview']);
            Route::post('/reviews/{review}', [CustomerCommerceController::class, 'updateReview']);
            Route::delete('/reviews/{review}', [CustomerCommerceController::class, 'destroyReview']);
            Route::get('/payments/{order}/payhere', [PayHereMobileController::class, 'checkout']);
            Route::get('/payments/{order}/status', [PayHereMobileController::class, 'status']);

            Route::patch('/profile', [CustomerAccountController::class, 'updateProfile']);
            Route::post('/profile/photo', [CustomerAccountController::class, 'uploadPhoto']);
            Route::patch('/profile/password', [CustomerAccountController::class, 'changePassword']);
            Route::delete('/profile', [CustomerAccountController::class, 'destroyAccount']);
            Route::delete('/profile', [CustomerAccountController::class, 'destroyAccount']);

            Route::get('/notifications', [CustomerAccountController::class, 'notifications']);
            Route::patch('/notifications/read-all', [CustomerAccountController::class, 'readAllNotifications']);
            Route::post('/notifications/device-token', [CustomerAccountController::class, 'saveDeviceToken']);
            Route::patch('/notifications/{notification}/read', [CustomerAccountController::class, 'readNotification']);
            Route::delete('/notifications/{notification}', [CustomerAccountController::class, 'deleteNotification']);

            Route::get('/support-tickets', [CustomerAccountController::class, 'tickets']);
            Route::post('/support-tickets', [CustomerAccountController::class, 'createTicket']);
            Route::get('/support-tickets/{ticket}', [CustomerAccountController::class, 'ticket']);
            Route::post('/support-tickets/{ticket}/replies', [CustomerAccountController::class, 'replyTicket']);
            Route::patch('/support-tickets/{ticket}/close', [CustomerAccountController::class, 'closeTicket']);
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
            Route::get('/dashboard', [VendorBusinessController::class, 'dashboard']);
            Route::get('/profile', [VendorBusinessController::class, 'profile']);
            Route::put('/profile', [VendorBusinessController::class, 'updateProfile']);
            Route::post('/profile/photo', [CustomerAccountController::class, 'uploadPhoto']);
            Route::patch('/profile/password', [CustomerAccountController::class, 'changePassword']);

            Route::get('/products', [VendorCatalogController::class, 'index']);
            Route::post('/products', [VendorCatalogController::class, 'store']);
            Route::get('/products/{product}', [VendorCatalogController::class, 'show']);
            Route::put('/products/{product}', [VendorCatalogController::class, 'update']);
            Route::delete('/products/{product}', [VendorCatalogController::class, 'destroy']);
            Route::post('/products/{product}/images', [VendorCatalogController::class, 'uploadImages']);
            Route::delete('/products/{product}/images/{image}', [VendorCatalogController::class, 'destroyImage']);
            Route::post('/products/{product}/variants', [VendorCatalogController::class, 'storeVariant']);
            Route::patch('/products/{product}/variants/{variant}', [VendorCatalogController::class, 'updateVariant']);
            Route::delete('/products/{product}/variants/{variant}', [VendorCatalogController::class, 'destroyVariant']);
            Route::get('/inventory', [VendorCatalogController::class, 'inventory']);
            Route::patch('/products/{product}/inventory', [VendorCatalogController::class, 'updateInventory']);

            Route::get('/orders', [VendorBusinessController::class, 'orders']);
            Route::get('/orders/{order}', [VendorBusinessController::class, 'order']);
            Route::patch('/orders/{order}/confirm', [VendorBusinessController::class, 'confirmOrder']);
            Route::patch('/orders/{order}/packed', [VendorBusinessController::class, 'packOrder']);
            Route::patch('/orders/{order}/cancel', [VendorBusinessController::class, 'cancelOrder']);
            Route::get('/earnings', [VendorBusinessController::class, 'earnings']);
            Route::get('/wallet', [VendorBusinessController::class, 'wallet']);
            Route::post('/payouts', [VendorBusinessController::class, 'requestPayout']);
            Route::get('/reviews', [VendorBusinessController::class, 'reviews']);
            Route::get('/refunds', [VendorBusinessController::class, 'refunds']);
            Route::patch('/refunds/{refund}/response', [VendorBusinessController::class, 'respondToRefund']);

            Route::get('/coupons', [VendorBusinessController::class, 'coupons']);
            Route::post('/coupons', [VendorBusinessController::class, 'storeCoupon']);
            Route::patch('/coupons/{coupon}', [VendorBusinessController::class, 'updateCoupon']);
            Route::delete('/coupons/{coupon}', [VendorBusinessController::class, 'destroyCoupon']);
            Route::get('/promotions', [VendorBusinessController::class, 'promotions']);
            Route::post('/promotions', [VendorBusinessController::class, 'storePromotion']);
            Route::post('/promotions/{promotion}', [VendorBusinessController::class, 'updatePromotion']);
            Route::delete('/promotions/{promotion}', [VendorBusinessController::class, 'destroyPromotion']);
            Route::get('/subscriptions', [VendorBusinessController::class, 'subscriptions']);
            Route::get('/scheduled-orders', [VendorBusinessController::class, 'scheduledOrders']);
            Route::get('/reports', [VendorBusinessController::class, 'reports']);

            Route::get('/notifications', [CustomerAccountController::class, 'notifications']);
            Route::patch('/notifications/read-all', [CustomerAccountController::class, 'readAllNotifications']);
            Route::post('/notifications/device-token', [CustomerAccountController::class, 'saveDeviceToken']);
            Route::patch('/notifications/{notification}/read', [CustomerAccountController::class, 'readNotification']);
            Route::delete('/notifications/{notification}', [CustomerAccountController::class, 'deleteNotification']);
            Route::get('/support-tickets', [CustomerAccountController::class, 'tickets']);
            Route::post('/support-tickets', [CustomerAccountController::class, 'createTicket']);
            Route::get('/support-tickets/{ticket}', [CustomerAccountController::class, 'ticket']);
            Route::post('/support-tickets/{ticket}/replies', [CustomerAccountController::class, 'replyTicket']);
            Route::patch('/support-tickets/{ticket}/close', [CustomerAccountController::class, 'closeTicket']);
        });
    });
});
