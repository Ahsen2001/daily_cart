<?php

use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDeliveryManagementController;
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
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentPageController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DeliveryEngineController;
use App\Http\Controllers\Admin\NewsletterManagementController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\RiderApprovalController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Customer\ScheduledOrderController;
use App\Http\Controllers\SupportTicketReplyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Super Admin,Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::patch('/customers/{customer}/status', [AdminCustomerController::class, 'updateStatus'])->name('customers.status');
    Route::delete('/customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
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
    Route::delete('/vendors/{vendor}', [VendorApprovalController::class, 'destroy'])->name('vendors.destroy');

    Route::get('/riders', [RiderApprovalController::class, 'index'])->name('riders.index');
    Route::patch('/riders/{rider}/approve', [RiderApprovalController::class, 'approve'])->name('riders.approve');
    Route::patch('/riders/{rider}/reject', [RiderApprovalController::class, 'reject'])->name('riders.reject');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('brands', AdminBrandController::class)->except(['show']);
    Route::get('/pages', [ContentPageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}/edit', [ContentPageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [ContentPageController::class, 'update'])->name('pages.update');
    Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{message}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::patch('/contact-messages/{message}', [ContactMessageController::class, 'update'])->name('contact-messages.update');
    Route::get('/newsletter', [NewsletterManagementController::class, 'index'])->name('newsletter.index');
    Route::patch('/newsletter/{subscription}', [NewsletterManagementController::class, 'update'])->name('newsletter.update');

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

    Route::get('/delivery-management/rules', [DeliveryEngineController::class, 'adminRules'])->name('delivery.rules.index');
    Route::post('/delivery-management/rules', [DeliveryEngineController::class, 'storeRule'])->name('delivery.rules.store');
    Route::put('/delivery-management/rules/{rule}', [DeliveryEngineController::class, 'updateRule'])->name('delivery.rules.update');
    Route::delete('/delivery-management/rules/{rule}', [DeliveryEngineController::class, 'destroyRule'])->name('delivery.rules.destroy');
    Route::get('/delivery-management/policies', [DeliveryEngineController::class, 'adminPolicies'])->name('delivery.policies');
    Route::post('/delivery-management/promotions', [DeliveryEngineController::class, 'storePromotion'])->name('delivery.promotions.store');
    Route::post('/delivery-management/free-delivery-rules', [DeliveryEngineController::class, 'storeFreeRule'])->name('delivery.free-rules.store');
    Route::post('/delivery-management/holidays', [DeliveryEngineController::class, 'storeHoliday'])->name('delivery.holidays.store');
    Route::put('/delivery-management/service-charge', [DeliveryEngineController::class, 'updateServiceCharge'])->name('delivery.service-charge.update');
    Route::delete('/delivery-management/policies/{type}/{id}', [DeliveryEngineController::class, 'destroyPolicy'])->name('delivery.policies.destroy');

    Route::get('/delivery-schedules', [AdminDeliveryManagementController::class, 'schedulesIndex'])->name('delivery-schedules.index');
    Route::patch('/delivery-schedules/{schedule}', [AdminDeliveryManagementController::class, 'schedulesUpdate'])->name('delivery-schedules.update');

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
