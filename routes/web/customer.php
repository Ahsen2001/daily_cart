<?php

use App\Http\Controllers\Customer\AdvertisementController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CouponController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\LoyaltyPointController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProductBrowseController;
use App\Http\Controllers\Customer\PromotionController;
use App\Http\Controllers\Customer\RefundController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\ScheduledOrderController;
use App\Http\Controllers\Customer\SubscriptionController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Integrations\PayHereController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'customer'])->name('dashboard');
    Route::get('/products', [ProductBrowseController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductBrowseController::class, 'show'])->name('products.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/wishlist/{wishlist}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::post('/checkout/loyalty', [CheckoutController::class, 'applyLoyalty'])->name('checkout.loyalty');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/receipt', [CustomerOrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/orders/{order}/payment', [PaymentController::class, 'show'])->name('payments.show');
    Route::patch('/payments/{payment}/method', [PaymentController::class, 'updateMethod'])->name('payments.method');
    Route::patch('/payments/{payment}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/{payment}/payhere', [PayHereController::class, 'checkout'])->name('payments.payhere');
    Route::get('/payments/{payment}/payhere/return', [PayHereController::class, 'return'])->name('payments.payhere.return');
    Route::get('/payments/{payment}/payhere/cancel', [PayHereController::class, 'cancel'])->name('payments.payhere.cancel');
    Route::get('/payments/{payment}/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/{payment}/failed', [PaymentController::class, 'failed'])->name('payments.failed');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/top-up', [WalletController::class, 'topUp'])->name('wallet.top-up');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');

    Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::get('/orders/{order}/refunds/create', [RefundController::class, 'create'])->name('refunds.create');
    Route::post('/orders/{order}/refunds', [RefundController::class, 'store'])->name('refunds.store');

    Route::get('/orders/{order}/products/{product}/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/orders/{order}/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/advertisements', [AdvertisementController::class, 'index'])->name('advertisements.index');
    Route::get('/loyalty', [LoyaltyPointController::class, 'index'])->name('loyalty.index');
    Route::get('/subscriptions/upcoming', [SubscriptionController::class, 'upcoming'])->name('subscriptions.upcoming');
    Route::resource('subscriptions', SubscriptionController::class)->except(['destroy']);
    Route::patch('/subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
    Route::patch('/subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    Route::patch('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('/scheduled-orders', [ScheduledOrderController::class, 'index'])->name('scheduled-orders.index');
    Route::patch('/scheduled-orders/{order}/cancel', [ScheduledOrderController::class, 'cancel'])->name('scheduled-orders.cancel');
});
