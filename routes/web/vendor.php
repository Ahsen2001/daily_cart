<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\ProductImageController;
use App\Http\Controllers\Vendor\ProductVariantController;
use App\Http\Controllers\Vendor\VendorCouponController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorEarningController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorPromotionController;
use App\Http\Controllers\Vendor\VendorRefundController;
use App\Http\Controllers\Vendor\VendorReportController;
use App\Http\Controllers\Vendor\VendorReviewController;
use App\Http\Controllers\Vendor\VendorSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/pending', [DashboardController::class, 'vendorPending'])->name('pending');

    Route::middleware('vendor.approved')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [VendorReportController::class, 'index'])->name('reports.index');
        Route::get('/subscriptions', [VendorSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/scheduled-orders', [VendorSubscriptionController::class, 'scheduledOrders'])->name('scheduled-orders.index');
        Route::resource('products', ProductController::class);
        Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock');
        Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::delete('/products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');

        Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/earnings', [VendorOrderController::class, 'earnings'])->name('orders.earnings');
        Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/confirm', [VendorOrderController::class, 'confirm'])->name('orders.confirm');
        Route::patch('/orders/{order}/packed', [VendorOrderController::class, 'packed'])->name('orders.packed');
        Route::patch('/orders/{order}/cancel', [VendorOrderController::class, 'cancel'])->name('orders.cancel');

        Route::get('/earnings', [VendorEarningController::class, 'index'])->name('earnings.index');
        Route::get('/refunds', [VendorRefundController::class, 'index'])->name('refunds.index');
        Route::get('/reviews', [VendorReviewController::class, 'index'])->name('reviews.index');
        Route::resource('coupons', VendorCouponController::class)->except(['show', 'destroy']);
        Route::resource('promotions', VendorPromotionController::class)->except(['show', 'destroy']);
    });
});
