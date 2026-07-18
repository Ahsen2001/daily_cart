<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Rider;
use App\Models\SupportTicket;
use App\Models\Vendor;

class DashboardService
{
    public function __construct(private readonly FinanceReportService $financeReportService) {}

    public function adminOverview(): array
    {
        $today = now()->toDateString();

        return [
            'total_customers' => Customer::query()->count('*'),
            'total_vendors' => Vendor::query()->count('*'),
            'pending_vendor_approvals' => Vendor::query()->where('status', 'pending')->count('*'),
            'total_riders' => Rider::query()->count('*'),
            'pending_rider_approvals' => Rider::query()->where('verification_status', 'pending')->count('*'),
            'total_products' => Product::query()->count('*'),
            'pending_product_approvals' => Product::query()->where('status', 'pending')->count('*'),
            'total_orders' => Order::query()->count('*'),
            'todays_orders' => Order::query()->whereDate('placed_at', '=', $today, 'and')->count('*'),
            'support_tickets_open' => SupportTicket::query()->where('status', 'open')->count('*'),
            'low_stock_products' => Product::query()->where('stock_quantity', '<=', 5)->count('*'),
        ];
    }

    public function superAdminOverview(): array
    {
        $today = now()->toDateString();
        $financeSummary = $this->financeReportService->adminSummary();

        return [
            'total_orders' => Order::query()->count('*'),
            'completed_orders' => Order::query()->where('order_status', 'delivered')->count('*'),
            'cancelled_orders' => Order::query()->where('order_status', 'cancelled')->count('*'),
            'total_revenue' => $this->completedRevenue(),
            'todays_revenue' => $this->completedRevenue($today, $today),
            'pending_cod_payments' => Order::query()->where('payment_status', 'pending')
                ->whereHas('payment', fn ($payment) => $payment->where('payment_method', 'cash_on_delivery'))
                ->sum('total_amount'),
            'total_refunds' => Refund::query()->where('status', 'approved')->sum('amount'),
            'total_vendor_payouts' => $financeSummary['total_vendor_payouts'] ?? 0.0,
            'total_rider_payouts' => $financeSummary['total_rider_payouts'] ?? 0.0,
            'active_promotions' => Promotion::query()->active()->count('*'),
            'active_coupons' => Coupon::query()->where('status', 'active')->count('*'),
        ];
    }

    public function vendorOverview(Vendor $vendor): array
    {
        return [
            'total_products' => $vendor->products()->count('*'),
            'pending_products' => $vendor->products()->where('status', 'pending')->count('*'),
            'approved_products' => $vendor->products()->where('status', 'approved')->count('*'),
            'total_orders' => $vendor->orders()->count('*'),
            'completed_orders' => $vendor->orders()->where('order_status', 'delivered')->count('*'),
            'cancelled_orders' => $vendor->orders()->where('order_status', 'cancelled')->count('*'),
            'revenue' => $this->completedRevenueForVendor($vendor),
            'earnings' => $this->financeReportService->vendorSummary($vendor)['completed'],
            'low_stock_products' => $vendor->products()->where('stock_quantity', '<=', 5)->count('*'),
            'customer_reviews' => Review::query()->where('vendor_id', $vendor->id)->count('*'),
        ];
    }

    public function riderOverview(Rider $rider): array
    {
        $today = now()->toDateString();
        $earnings = app(RiderEarningService::class)->summary($rider);

        return [
            'assigned_deliveries' => $rider->deliveries()->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])->count('*'),
            'completed_deliveries' => $rider->deliveries()->where('status', 'delivered')->count('*'),
            'failed_deliveries' => $rider->deliveries()->where('status', 'failed')->count('*'),
            'todays_deliveries' => $rider->deliveries()->whereDate('created_at', '=', $today, 'and')->count('*'),
            'daily_earnings' => $earnings['daily'],
            'weekly_earnings' => $earnings['weekly'],
            'monthly_earnings' => $earnings['monthly'],
        ];
    }

    private function completedRevenue(?string $from = null, ?string $to = null): float
    {
        return (float) Order::query()
            ->where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->when($from, fn ($query) => $query->whereDate('placed_at', '>=', $from, 'and'))
            ->when($to, fn ($query) => $query->whereDate('placed_at', '<=', $to, 'and'))
            ->sum('total_amount');
    }

    private function completedRevenueForVendor(Vendor $vendor): float
    {
        return (float) $vendor->orders()
            ->where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

}
