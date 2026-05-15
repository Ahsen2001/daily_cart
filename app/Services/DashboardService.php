<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Rider;
use App\Models\SupportTicket;
use App\Models\Vendor;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(private readonly FinanceReportService $financeReportService) {}

    public function adminOverview(): array
    {
        $today = now()->toDateString();

        return [
            'total_customers' => Customer::count(),
            'total_vendors' => Vendor::count(),
            'pending_vendor_approvals' => Vendor::where('status', 'pending')->count(),
            'total_riders' => Rider::count(),
            'pending_rider_approvals' => Rider::where('verification_status', 'pending')->count(),
            'total_products' => Product::count(),
            'pending_product_approvals' => Product::where('status', 'pending')->count(),
            'total_orders' => Order::count(),
            'todays_orders' => Order::whereDate('placed_at', $today)->count(),
            'completed_orders' => Order::where('order_status', 'delivered')->count(),
            'cancelled_orders' => Order::where('order_status', 'cancelled')->count(),
            'total_revenue' => $this->completedRevenue(),
            'todays_revenue' => $this->completedRevenue($today, $today),
            'pending_cod_payments' => Order::where('payment_status', 'pending')
                ->whereHas('payment', fn ($payment) => $payment->where('payment_method', 'cash_on_delivery'))
                ->sum('total_amount'),
            'total_refunds' => Refund::where('status', 'approved')->sum('amount'),
            'support_tickets_open' => SupportTicket::where('status', 'open')->count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', 5)->count(),
        ];
    }

    public function vendorOverview(Vendor $vendor): array
    {
        return [
            'total_products' => $vendor->products()->count(),
            'pending_products' => $vendor->products()->where('status', 'pending')->count(),
            'approved_products' => $vendor->products()->where('status', 'approved')->count(),
            'total_orders' => $vendor->orders()->count(),
            'completed_orders' => $vendor->orders()->where('order_status', 'delivered')->count(),
            'cancelled_orders' => $vendor->orders()->where('order_status', 'cancelled')->count(),
            'revenue' => $this->completedRevenueForVendor($vendor),
            'earnings' => $this->financeReportService->vendorSummary($vendor)['completed'],
            'low_stock_products' => $vendor->products()->where('stock_quantity', '<=', 5)->count(),
            'customer_reviews' => $vendor->products()->whereHas('reviews')->withCount('reviews')->get()->sum('reviews_count'),
        ];
    }

    public function riderOverview(Rider $rider): array
    {
        $today = now()->toDateString();

        return [
            'assigned_deliveries' => $rider->deliveries()->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])->count(),
            'completed_deliveries' => $rider->deliveries()->where('status', 'delivered')->count(),
            'failed_deliveries' => $rider->deliveries()->where('status', 'failed')->count(),
            'todays_deliveries' => $rider->deliveries()->whereDate('created_at', $today)->count(),
            'daily_earnings' => $this->riderEarnings($rider, now()->startOfDay(), now()->endOfDay()),
            'weekly_earnings' => $this->riderEarnings($rider, now()->startOfWeek(), now()->endOfWeek()),
            'monthly_earnings' => $this->riderEarnings($rider, now()->startOfMonth(), now()->endOfMonth()),
        ];
    }

    private function completedRevenue(?string $from = null, ?string $to = null): float
    {
        return (float) Order::query()
            ->where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->when($from, fn ($query) => $query->whereDate('placed_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('placed_at', '<=', $to))
            ->sum('total_amount');
    }

    private function completedRevenueForVendor(Vendor $vendor): float
    {
        return (float) $vendor->orders()
            ->where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    private function riderEarnings(Rider $rider, Carbon $from, Carbon $to): float
    {
        return $rider->deliveries()
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$from, $to])
            ->count() * FinanceReportService::RIDER_DELIVERY_EARNING;
    }
}
