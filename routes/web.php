<?php

use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\RiderApprovalController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProductBrowseController;
use App\Http\Controllers\Customer\RefundController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Rider\RiderDeliveryController;
use App\Http\Controllers\Rider\RiderEarningController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\ProductImageController;
use App\Http\Controllers\Vendor\ProductVariantController;
use App\Http\Controllers\Vendor\VendorEarningController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorRefundController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'superAdmin'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:Super Admin,Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    Route::get('/vendors', [VendorApprovalController::class, 'index'])->name('vendors.index');
    Route::patch('/vendors/{vendor}/approve', [VendorApprovalController::class, 'approve'])->name('vendors.approve');
    Route::patch('/vendors/{vendor}/reject', [VendorApprovalController::class, 'reject'])->name('vendors.reject');

    Route::get('/riders', [RiderApprovalController::class, 'index'])->name('riders.index');
    Route::patch('/riders/{rider}/approve', [RiderApprovalController::class, 'approve'])->name('riders.approve');
    Route::patch('/riders/{rider}/reject', [RiderApprovalController::class, 'reject'])->name('riders.reject');

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [AdminProductController::class, 'show'])->name('products.show');
    Route::patch('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::patch('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
    Route::patch('/products/{product}/feature', [AdminProductController::class, 'feature'])->name('products.feature');
    Route::patch('/products/{product}/status', [AdminProductController::class, 'status'])->name('products.status');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'status'])->name('orders.status');
    Route::get('/orders/{order}/assign-rider', [AdminOrderController::class, 'assignRiderForm'])->name('orders.assign-rider');
    Route::patch('/orders/{order}/assign-rider', [AdminOrderController::class, 'assignRider'])->name('orders.assign-rider.store');

    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');

    Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
    Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
    Route::patch('/refunds/{refund}/approve', [AdminRefundController::class, 'approve'])->name('refunds.approve');
    Route::patch('/refunds/{refund}/reject', [AdminRefundController::class, 'reject'])->name('refunds.reject');
});

Route::middleware(['auth', 'verified', 'role:Vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/pending', [DashboardController::class, 'vendorPending'])->name('pending');

    Route::middleware('vendor.approved')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'vendor'])->name('dashboard');
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
    });
});

Route::middleware(['auth', 'verified', 'role:Rider'])->prefix('rider')->name('rider.')->group(function () {
    Route::get('/pending', [DashboardController::class, 'riderPending'])->name('pending');

    Route::middleware('rider.approved')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'rider'])->name('dashboard');
        Route::get('/deliveries', [RiderDeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/earnings', [RiderDeliveryController::class, 'earnings'])->name('deliveries.earnings');
        Route::get('/deliveries/{delivery}', [RiderDeliveryController::class, 'show'])->name('deliveries.show');
        Route::patch('/deliveries/{delivery}/accept', [RiderDeliveryController::class, 'accept'])->name('deliveries.accept');
        Route::patch('/deliveries/{delivery}/picked-up', [RiderDeliveryController::class, 'pickedUp'])->name('deliveries.picked-up');
        Route::patch('/deliveries/{delivery}/on-the-way', [RiderDeliveryController::class, 'onTheWay'])->name('deliveries.on-the-way');
        Route::post('/deliveries/{delivery}/delivered', [RiderDeliveryController::class, 'delivered'])->name('deliveries.delivered');
        Route::patch('/deliveries/{delivery}/failed', [RiderDeliveryController::class, 'failed'])->name('deliveries.failed');
        Route::post('/location', [RiderDeliveryController::class, 'location'])->name('location.store');
        Route::get('/earnings', [RiderEarningController::class, 'index'])->name('earnings.index');
    });
});

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
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/orders/{order}/payment', [PaymentController::class, 'show'])->name('payments.show');
    Route::patch('/payments/{payment}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/{payment}/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/{payment}/failed', [PaymentController::class, 'failed'])->name('payments.failed');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/top-up', [WalletController::class, 'topUp'])->name('wallet.top-up');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');

    Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::get('/orders/{order}/refunds/create', [RefundController::class, 'create'])->name('refunds.create');
    Route::post('/orders/{order}/refunds', [RefundController::class, 'store'])->name('refunds.store');
});

require __DIR__.'/auth.php';
