<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduledOrderService
{
    public function customerOrders(Customer $customer, array $filters = [])
    {
        return $this->scheduledQuery()
            ->where('customer_id', $customer->id)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->latest('scheduled_delivery_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function adminOrders(array $filters = [])
    {
        return $this->scheduledQuery()
            ->with(['customer.user', 'vendor', 'delivery.rider.user'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('scheduled_delivery_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('scheduled_delivery_at', '<=', $date))
            ->latest('scheduled_delivery_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function vendorOrders(int $vendorId, array $filters = [])
    {
        return $this->scheduledQuery()
            ->where('vendor_id', $vendorId)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->latest('scheduled_delivery_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function cancelPending(Order $order, Customer $customer): Order
    {
        if ($order->customer_id !== $customer->id || ! $order->scheduled_delivery_at) {
            abort(403);
        }

        if ($order->order_status !== 'pending') {
            throw ValidationException::withMessages([
                'order_status' => 'Only pending scheduled orders can be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => 'cancelled',
                'cancellation_reason' => 'Customer cancelled scheduled future order.',
            ]);

            $order->delivery?->update(['status' => 'cancelled']);

            return $order->refresh();
        });
    }

    public function validateScheduledAt(string $scheduledAt): Carbon
    {
        $scheduled = Carbon::parse($scheduledAt);

        if ($scheduled->lt(now()->addMinutes(30))) {
            throw ValidationException::withMessages([
                'scheduled_delivery_at' => 'Delivery time must be at least 30 minutes after placing the order.',
            ]);
        }

        return $scheduled;
    }

    private function scheduledQuery()
    {
        return Order::with(['vendor', 'customer.user', 'items.product'])
            ->whereNotNull('scheduled_delivery_at')
            ->where('scheduled_delivery_at', '>=', now());
    }
}
