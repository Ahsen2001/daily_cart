<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Rider;
use App\Models\SupportTicket;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(private readonly FinanceReportService $financeReportService) {}

    public function filters(): array
    {
        return [
            'vendors' => Vendor::orderBy('store_name')->get(['id', 'store_name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::with('user:id,name')->latest()->limit(200)->get(['id', 'user_id', 'first_name', 'last_name']),
            'riders' => Rider::with('user:id,name')->latest()->limit(200)->get(['id', 'user_id']),
        ];
    }

    public function sales(array $filters): array
    {
        $orders = $this->filteredOrders($filters)->with(['customer.user', 'vendor', 'payment'])->latest('placed_at');

        return [
            'summary' => [
                'daily_sales' => $this->periodSales('day', $filters),
                'weekly_sales' => $this->periodSales('week', $filters),
                'monthly_sales' => $this->periodSales('month', $filters),
                'yearly_sales' => $this->periodSales('year', $filters),
                'date_range_sales' => (float) (clone $this->filteredOrders($filters))->where('order_status', 'delivered')->where('payment_status', 'paid')->sum('total_amount'),
                'total_orders' => (clone $this->filteredOrders($filters))->count(),
            ],
            'orders' => $orders->paginate(15)->withQueryString(),
            'by_vendor' => $this->salesByVendor($filters),
            'by_category' => $this->salesByCategory($filters),
            'by_payment_method' => $this->salesByPaymentMethod($filters),
            'by_order_status' => $this->salesByOrderStatus($filters),
            'filters' => $this->filters(),
        ];
    }

    public function products(array $filters): array
    {
        return [
            'best_selling' => DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.order_status', 'delivered')
                ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('products.category_id', $categoryId))
                ->when($filters['vendor_id'] ?? null, fn ($query, $vendorId) => $query->where('products.vendor_id', $vendorId))
                ->selectRaw('products.name, SUM(order_items.quantity) as sold_quantity, SUM(order_items.total_price) as revenue')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('sold_quantity')
                ->limit(15)
                ->get(),
            'low_stock' => Product::with(['vendor', 'category'])->where('stock_quantity', '<=', 5)->orderBy('stock_quantity')->paginate(15, ['*'], 'low_stock_page')->withQueryString(),
            'out_of_stock' => Product::with(['vendor', 'category'])->where('stock_quantity', '<=', 0)->paginate(15, ['*'], 'out_stock_page')->withQueryString(),
            'near_expiry' => Product::with(['vendor', 'category'])
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->whereDate('expiry_date', '<=', now()->addDays(14)->toDateString())
                ->orderBy('expiry_date')
                ->paginate(15, ['*'], 'expiry_page')
                ->withQueryString(),
            'most_reviewed' => Product::withCount(['reviews' => fn ($query) => $query->where('status', 'visible')])
                ->orderByDesc('reviews_count')
                ->limit(15)
                ->get(),
            'highest_rated' => Product::withAvg(['reviews' => fn ($query) => $query->where('status', 'visible')], 'rating')
                ->having('reviews_avg_rating', '>', 0)
                ->orderByDesc('reviews_avg_rating')
                ->limit(15)
                ->get(),
            'filters' => $this->filters(),
        ];
    }

    public function vendors(array $filters): array
    {
        return [
            'rows' => Vendor::with('user')
                ->withCount(['orders', 'products', 'products as pending_products_count' => fn ($query) => $query->where('status', 'pending')])
                ->paginate(15)
                ->withQueryString(),
            'top_vendors' => $this->salesByVendor($filters)->take(15),
            'commission_report' => Vendor::with('user')->get()->map(fn (Vendor $vendor) => [
                'vendor' => $vendor,
                'earnings' => $this->financeReportService->vendorSummary($vendor, $filters['from'] ?? null, $filters['to'] ?? null)['completed'],
                'commission_rate' => (float) $vendor->commission_rate,
            ]),
            'filters' => $this->filters(),
        ];
    }

    public function customers(array $filters): array
    {
        return [
            'new_customers' => Customer::with('user')->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))->latest()->paginate(15)->withQueryString(),
            'active_customers' => Customer::with('user')->whereHas('orders', fn ($query) => $this->dateRange($query, 'placed_at', $filters)->where('order_status', 'delivered'))->count(),
            'repeat_customers' => Customer::whereHas('orders', fn ($query) => $query->where('order_status', 'delivered'), '>=', 2)->count(),
            'top_spending' => Customer::with('user')
                ->withSum(['orders as paid_total' => fn ($query) => $query->where('order_status', 'delivered')->where('payment_status', 'paid')], 'total_amount')
                ->orderByDesc('paid_total')
                ->limit(15)
                ->get(),
            'support_counts' => Customer::with('user')
                ->withCount(['user as support_tickets_count' => fn ($query) => $query->join('support_tickets', 'support_tickets.user_id', '=', 'users.id')])
                ->limit(15)
                ->get(),
            'filters' => $this->filters(),
        ];
    }

    public function riders(array $filters): array
    {
        return [
            'rows' => Rider::with('user')->withCount([
                'deliveries',
                'deliveries as completed_deliveries_count' => fn ($query) => $query->where('status', 'delivered'),
                'deliveries as failed_deliveries_count' => fn ($query) => $query->where('status', 'failed'),
            ])->paginate(15)->withQueryString(),
            'top_riders' => Rider::with('user')
                ->withCount(['deliveries as completed_deliveries_count' => fn ($query) => $query->where('status', 'delivered')])
                ->orderByDesc('completed_deliveries_count')
                ->limit(15)
                ->get(),
            'average_completion_minutes' => (float) Delivery::query()
                ->whereNotNull('picked_up_at')
                ->whereNotNull('delivered_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, picked_up_at, delivered_at)')),
            'assignment_history' => Delivery::with(['rider.user', 'order.customer.user'])
                ->latest()
                ->paginate(15, ['*'], 'history_page')
                ->withQueryString(),
            'filters' => $this->filters(),
        ];
    }

    public function finance(array $filters): array
    {
        $summary = $this->financeReportService->adminSummary($filters['from'] ?? null, $filters['to'] ?? null);

        return [
            'summary' => $summary + [
                'platform_commission' => max(0, $summary['total_revenue'] - $summary['total_vendor_payouts']),
                'failed_payment_amount' => (float) DB::table('payments')->where('status', 'failed')->sum('amount'),
                'paid_amount' => (float) DB::table('payments')->where('status', 'paid')->sum('amount'),
            ],
            'refunds' => Refund::with(['order.customer.user'])->latest()->paginate(15)->withQueryString(),
            'filters' => $this->filters(),
        ];
    }

    public function support(array $filters): array
    {
        return [
            'summary' => [
                'open' => SupportTicket::where('status', 'open')->count(),
                'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
                'resolved' => SupportTicket::where('status', 'resolved')->count(),
                'closed' => SupportTicket::where('status', 'closed')->count(),
                'high_priority' => SupportTicket::whereIn('priority', ['high', 'urgent'])->count(),
                'average_response_time' => 'Placeholder',
            ],
            'tickets' => SupportTicket::with(['user', 'assignedAdmin'])
                ->when($filters['order_status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => $this->filters(),
        ];
    }

    public function vendorPrivate(Vendor $vendor, array $filters): array
    {
        return [
            'summary' => app(DashboardService::class)->vendorOverview($vendor),
            'orders' => $this->dateRange($vendor->orders()->with(['customer.user', 'payment']), 'placed_at', $filters)
                ->latest('placed_at')
                ->paginate(15)
                ->withQueryString(),
            'best_selling' => DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.vendor_id', $vendor->id)
                ->where('orders.order_status', 'delivered')
                ->selectRaw('order_items.product_name, SUM(order_items.quantity) as sold_quantity, SUM(order_items.total_price) as revenue')
                ->groupBy('order_items.product_name')
                ->orderByDesc('sold_quantity')
                ->limit(10)
                ->get(),
            'reviews' => $vendor->products()->with(['reviews.customer.user'])->whereHas('reviews')->latest()->limit(10)->get(),
            'low_stock' => $vendor->products()->where('stock_quantity', '<=', 5)->orderBy('stock_quantity')->get(),
        ];
    }

    public function riderPrivate(Rider $rider, array $filters): array
    {
        return [
            'summary' => app(DashboardService::class)->riderOverview($rider),
            'deliveries' => $this->dateRange($rider->deliveries()->with(['order.customer.user']), 'created_at', $filters)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'earnings' => [
                'per_delivery' => FinanceReportService::RIDER_DELIVERY_EARNING,
                'completed_count' => $rider->deliveries()->where('status', 'delivered')->count(),
            ],
        ];
    }

    public function summaryRows(array $summary): array
    {
        return collect($summary)->map(fn ($value, $key) => [str_replace('_', ' ', (string) $key), $value])->values()->all();
    }

    private function filteredOrders(array $filters): Builder
    {
        return $this->dateRange(Order::query(), 'placed_at', $filters)
            ->when($filters['vendor_id'] ?? null, fn ($query, $vendorId) => $query->where('vendor_id', $vendorId))
            ->when($filters['customer_id'] ?? null, fn ($query, $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['order_status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->whereHas('payment', fn ($payment) => $payment->where('payment_method', $method)));
    }

    private function dateRange($query, string $column, array $filters)
    {
        return $query
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate($column, '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate($column, '<=', $date));
    }

    private function periodSales(string $period, array $filters): float
    {
        return (float) $this->filteredOrders($filters)
            ->where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->when($period === 'day', fn ($query) => $query->whereDate('placed_at', now()->toDateString()))
            ->when($period === 'week', fn ($query) => $query->whereBetween('placed_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($period === 'month', fn ($query) => $query->whereYear('placed_at', now()->year)->whereMonth('placed_at', now()->month))
            ->when($period === 'year', fn ($query) => $query->whereYear('placed_at', now()->year))
            ->sum('total_amount');
    }

    private function salesByVendor(array $filters)
    {
        return DB::table('orders')
            ->join('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->where('orders.order_status', 'delivered')
            ->where('orders.payment_status', 'paid')
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('orders.placed_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('orders.placed_at', '<=', $date))
            ->selectRaw('vendors.store_name as label, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as revenue')
            ->groupBy('vendors.id', 'vendors.store_name')
            ->orderByDesc('revenue')
            ->get();
    }

    private function salesByCategory(array $filters)
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.order_status', 'delivered')
            ->where('orders.payment_status', 'paid')
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('categories.id', $categoryId))
            ->selectRaw('categories.name as label, SUM(order_items.total_price) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();
    }

    private function salesByPaymentMethod(array $filters)
    {
        return DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.order_status', 'delivered')
            ->selectRaw('payments.payment_method as label, SUM(payments.amount) as revenue')
            ->groupBy('payments.payment_method')
            ->orderByDesc('revenue')
            ->get();
    }

    private function salesByOrderStatus(array $filters)
    {
        return $this->filteredOrders($filters)
            ->selectRaw('order_status as label, COUNT(*) as orders_count')
            ->groupBy('order_status')
            ->orderByDesc('orders_count')
            ->get();
    }
}
