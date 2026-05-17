<?php

use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminLoyaltySettingController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminPromotionController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminSupportTicketController;
use App\Http\Controllers\Admin\AdvertisementController as AdminAdvertisementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\RiderApprovalController;
use App\Http\Controllers\Admin\VendorApprovalController;
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
use App\Http\Controllers\Integrations\GoogleMapsController;
use App\Http\Controllers\Integrations\PayHereController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Rider\RiderDashboardController;
use App\Http\Controllers\Rider\RiderDeliveryController;
use App\Http\Controllers\Rider\RiderEarningController;
use App\Http\Controllers\Rider\RiderReportController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportTicketReplyController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.subscribe');
Route::post('/payments/payhere/notify', [PayHereController::class, 'notify'])->name('payhere.notify');

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'markUnread'])->name('notifications.unread');

    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support.tickets.index');
    Route::get('/support-tickets/create', [SupportTicketController::class, 'create'])->name('support.tickets.create');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support.tickets.store');
    Route::get('/support-tickets/{ticket}', [SupportTicketController::class, 'show'])->name('support.tickets.show');
    Route::post('/support-tickets/{ticket}/replies', [SupportTicketReplyController::class, 'store'])->name('support.tickets.replies.store');
});

Route::middleware(['auth', 'verified', 'role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:Super Admin,Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/products', [AdminReportController::class, 'products'])->name('reports.products');
    Route::get('/reports/vendors', [AdminReportController::class, 'vendors'])->name('reports.vendors');
    Route::get('/reports/customers', [AdminReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/riders', [AdminReportController::class, 'riders'])->name('reports.riders');
    Route::get('/reports/finance', [AdminReportController::class, 'finance'])->name('reports.finance');
    Route::get('/reports/support', [AdminReportController::class, 'support'])->name('reports.support');

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

    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/support-tickets', [AdminSupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/{ticket}', [AdminSupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::patch('/support-tickets/{ticket}', [AdminSupportTicketController::class, 'update'])->name('support-tickets.update');
    Route::post('/support-tickets/{ticket}/replies', [SupportTicketReplyController::class, 'store'])->name('support-tickets.replies.store');

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/hide', [AdminReviewController::class, 'hide'])->name('reviews.hide');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::resource('coupons', AdminCouponController::class)->except(['show', 'destroy']);
    Route::resource('promotions', AdminPromotionController::class)->except(['show', 'destroy']);
    Route::resource('advertisements', AdminAdvertisementController::class)->except(['show', 'destroy']);
    Route::get('/loyalty-settings', [AdminLoyaltySettingController::class, 'edit'])->name('loyalty-settings.edit');
    Route::patch('/loyalty-settings', [AdminLoyaltySettingController::class, 'update'])->name('loyalty-settings.update');

    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::patch('/subscriptions/{subscription}/pause', [AdminSubscriptionController::class, 'pause'])->name('subscriptions.pause');
    Route::patch('/subscriptions/{subscription}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('/subscription-products', [AdminSubscriptionController::class, 'eligibleProducts'])->name('subscriptions.products');
    Route::patch('/subscription-products/{product}', [AdminSubscriptionController::class, 'updateEligibility'])->name('subscriptions.products.update');
    Route::get('/scheduled-orders', [ScheduledOrderController::class, 'admin'])->name('scheduled-orders.index');
});

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

Route::middleware(['auth', 'verified', 'role:Rider'])->prefix('rider')->name('rider.')->group(function () {
    Route::get('/pending', [DashboardController::class, 'riderPending'])->name('pending');

    Route::middleware('rider.approved')->group(function () {
        Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [RiderReportController::class, 'index'])->name('reports.index');
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
    Route::post('/checkout/loyalty', [CheckoutController::class, 'applyLoyalty'])->name('checkout.loyalty');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/receipt', [CustomerOrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/orders/{order}/payment', [PaymentController::class, 'show'])->name('payments.show');
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

Route::middleware(['auth', 'verified'])->prefix('integrations/maps')->name('maps.')->group(function () {
    Route::post('/geocode', [GoogleMapsController::class, 'geocode'])->name('geocode');
    Route::post('/reverse-geocode', [GoogleMapsController::class, 'reverseGeocode'])->name('reverse-geocode');
    Route::post('/distance', [GoogleMapsController::class, 'distance'])->name('distance');
});

require __DIR__.'/auth.php';
