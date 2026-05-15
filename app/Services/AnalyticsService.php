<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function adminCharts(array $filters = []): array
    {
        return [
            'revenue_line' => $this->dailyRevenue($filters),
            'orders_bar' => $this->dailyOrders($filters),
            'category_sales_pie' => $this->categorySales($filters),
            'payment_method_chart' => $this->paymentMethodSales($filters),
        ];
    }

    public function vendorCharts(int $vendorId, array $filters = []): array
    {
        $filters['vendor_id'] = $vendorId;

        return [
            'revenue_line' => $this->dailyRevenue($filters),
            'orders_bar' => $this->dailyOrders($filters),
        ];
    }

    public function dailyRevenue(array $filters = []): array
    {
        $rows = $this->filteredOrders($filters)
            ->selectRaw('DATE(placed_at) as label, SUM(total_amount) as value')
            ->where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->groupBy('label')
            ->orderBy('label')
            ->limit(60)
            ->get();

        return $this->chartPair($rows);
    }

    public function dailyOrders(array $filters = []): array
    {
        $rows = $this->filteredOrders($filters)
            ->selectRaw('DATE(placed_at) as label, COUNT(*) as value')
            ->groupBy('label')
            ->orderBy('label')
            ->limit(60)
            ->get();

        return $this->chartPair($rows);
    }

    public function categorySales(array $filters = []): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.order_status', 'delivered')
            ->where('orders.payment_status', 'paid')
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('orders.placed_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('orders.placed_at', '<=', $date))
            ->when($filters['vendor_id'] ?? null, fn ($query, $vendorId) => $query->where('orders.vendor_id', $vendorId))
            ->selectRaw('categories.name as label, SUM(order_items.total_price) as value')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        return $this->chartPair($rows);
    }

    public function paymentMethodSales(array $filters = []): array
    {
        $rows = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.order_status', 'delivered')
            ->whereIn('payments.status', ['paid', 'refunded'])
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('orders.placed_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('orders.placed_at', '<=', $date))
            ->selectRaw('payments.payment_method as label, SUM(payments.amount) as value')
            ->groupBy('payments.payment_method')
            ->orderByDesc('value')
            ->get();

        return $this->chartPair($rows);
    }

    private function filteredOrders(array $filters)
    {
        return Order::query()
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('placed_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('placed_at', '<=', $date))
            ->when($filters['vendor_id'] ?? null, fn ($query, $vendorId) => $query->where('vendor_id', $vendorId))
            ->when($filters['customer_id'] ?? null, fn ($query, $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['order_status'] ?? null, fn ($query, $status) => $query->where('order_status', $status));
    }

    private function chartPair($rows): array
    {
        return [
            'labels' => $rows->pluck('label')->map(fn ($label) => str_replace('_', ' ', (string) $label))->all(),
            'values' => $rows->pluck('value')->map(fn ($value) => round((float) $value, 2))->all(),
        ];
    }
}
