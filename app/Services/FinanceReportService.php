<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FinanceReportService
{
    public const RIDER_DELIVERY_EARNING = 350.00;

    public function adminSummary(?string $from = null, ?string $to = null): array
    {
        $orders = $this->dateRange(Order::query(), 'placed_at', $from, $to);
        $deliveredOrders = (clone $orders)->where('order_status', 'delivered');

        $vendorPayouts = $deliveredOrders->get()->sum(fn (Order $order) => $this->vendorEarning($order));

        return [
            'total_revenue' => (float) (clone $orders)->whereIn('payment_status', ['paid', 'refunded'])->sum('total_amount'),
            'total_delivery_charges' => (float) (clone $orders)->sum('delivery_fee'),
            'total_service_charges' => (float) (clone $orders)->sum('service_charge'),
            'total_vendor_payouts' => (float) $vendorPayouts,
            'total_rider_payouts' => (float) $this->dateRange(Order::query(), 'placed_at', $from, $to)
                ->where('order_status', 'delivered')
                ->count() * self::RIDER_DELIVERY_EARNING,
            'total_refunds' => (float) $this->dateRange(Refund::query(), 'processed_at', $from, $to)
                ->where('status', 'approved')
                ->sum('amount'),
            'total_cod_pending_payments' => (float) $this->dateRange(Payment::query(), 'created_at', $from, $to)
                ->where('payment_method', 'cash_on_delivery')
                ->where('status', 'pending')
                ->sum('amount'),
            'total_paid_orders' => (int) $this->dateRange(Order::query(), 'placed_at', $from, $to)
                ->where('payment_status', 'paid')
                ->count(),
        ];
    }

    public function vendorSummary(Vendor $vendor, ?string $from = null, ?string $to = null): array
    {
        $orders = $this->dateRange(Order::query()->where('vendor_id', $vendor->id), 'placed_at', $from, $to);
        $delivered = (clone $orders)->where('order_status', 'delivered')->get();

        return [
            'pending' => (float) (clone $orders)->whereIn('order_status', ['pending', 'confirmed', 'packed', 'assigned_to_rider', 'out_for_delivery'])->sum('total_amount'),
            'completed' => (float) $delivered->sum(fn (Order $order) => $this->vendorEarning($order)),
            'refunded' => (float) (clone $orders)->where('order_status', 'refunded')->sum('total_amount'),
            'orders' => $this->dateRange(Order::query()->with('payment')->where('vendor_id', $vendor->id), 'placed_at', $from, $to)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ];
    }

    public function vendorEarning(Order $order): float
    {
        if ($order->order_status === 'refunded') {
            return 0.0;
        }

        $commissionRate = (float) ($order->vendor?->commission_rate ?? 0);
        $base = (float) $order->subtotal - (float) $order->discount_amount;

        return round($base - ($base * ($commissionRate / 100)), 2);
    }

    private function dateRange(Builder $query, string $column, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->whereDate($column, '>=', Carbon::parse($from)->toDateString()))
            ->when($to, fn (Builder $query) => $query->whereDate($column, '<=', Carbon::parse($to)->toDateString()));
    }
}
