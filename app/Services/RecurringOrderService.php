<?php

namespace App\Services;

use App\Jobs\SubscriptionOrderJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecurringOrderService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly PaymentService $payments,
        private readonly SubscriptionService $subscriptions,
    ) {}

    public function dispatchDueSubscriptions(): int
    {
        $count = 0;

        Subscription::with(['customer.user', 'vendor.user', 'product', 'variant'])
            ->where('status', 'active')
            ->whereDate('next_delivery_date', '<=', now()->toDateString())
            ->chunkById(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    SubscriptionOrderJob::dispatch($subscription->id);
                    $count++;
                }
            });

        return $count;
    }

    public function generate(Subscription $subscription): Order
    {
        if ($subscription->status !== 'active') {
            throw ValidationException::withMessages(['subscription' => 'Only active subscriptions can generate recurring orders.']);
        }

        return DB::transaction(function () use ($subscription) {
            $subscription = Subscription::whereKey($subscription->id)->lockForUpdate()->with(['customer.user', 'vendor.user', 'product', 'variant'])->firstOrFail();
            $product = Product::whereKey($subscription->product_id)->lockForUpdate()->first();
            $variant = $subscription->variant;

            try {
                $this->subscriptions->ensureProductEligible($product, $subscription->quantity, $variant);
            } catch (ValidationException $exception) {
                $this->markFailed($subscription, $exception->getMessage());
                throw $exception;
            }

            $scheduledAt = $this->scheduledDateTime($subscription);
            $subtotal = round((float) $subscription->unit_price * $subscription->quantity, 2);
            $deliveryFee = OrderService::DELIVERY_CHARGE;
            $serviceCharge = round($subtotal * OrderService::SERVICE_CHARGE_RATE, 2);
            $total = round($subtotal + $deliveryFee + $serviceCharge, 2);

            $order = Order::create([
                'order_number' => $this->orderNumber(),
                'customer_id' => $subscription->customer_id,
                'vendor_id' => $subscription->vendor_id,
                'subscription_id' => $subscription->id,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'loyalty_points_redeemed' => 0,
                'loyalty_discount_amount' => 0,
                'delivery_fee' => $deliveryFee,
                'service_charge' => $serviceCharge,
                'tax_amount' => 0,
                'total_amount' => $total,
                'currency' => CurrencyService::CURRENCY,
                'delivery_address' => $subscription->delivery_address,
                'order_status' => 'pending',
                'payment_status' => 'pending',
                'placed_at' => now(),
                'scheduled_delivery_at' => $scheduledAt,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'vendor_id' => $product->vendor_id,
                'product_name' => $product->name.($variant ? ' - '.$variant->name : ''),
                'quantity' => $subscription->quantity,
                'unit_price' => $subscription->unit_price,
                'total_price' => $subtotal,
            ]);

            $this->payments->createPlaceholder($order, $subscription->payment_method);
            $order->delivery()->create([
                'pickup_address' => $subscription->vendor->address,
                'delivery_address' => $subscription->delivery_address,
                'scheduled_at' => $scheduledAt,
                'status' => 'pending',
            ]);

            $subscription->update([
                'last_generated_at' => now(),
                'failed_reason' => null,
                'next_delivery_date' => $this->nextDate($subscription),
            ]);

            $this->subscriptions->completeIfEnded($subscription->refresh());
            $this->notifications->send($subscription->customer->user, 'Recurring order created', 'Order '.$order->order_number.' was created from your subscription.', 'recurring_order_created');
            $this->notifications->send($subscription->vendor->user, 'Recurring order created', 'Order '.$order->order_number.' is waiting for confirmation.', 'recurring_order_created');

            return $order->refresh();
        });
    }

    public function markFailed(Subscription $subscription, string $reason): void
    {
        $subscription->update(['failed_reason' => Str::limit($reason, 1000)]);
        $this->notifications->send($subscription->customer->user, 'Recurring order failed', $reason, 'recurring_order_failed');
        $this->notifications->send($subscription->vendor->user, 'Subscription low stock alert', $reason, 'subscription_low_stock');
    }

    private function scheduledDateTime(Subscription $subscription): Carbon
    {
        $scheduled = Carbon::parse($subscription->next_delivery_date->format('Y-m-d').' '.$subscription->preferred_delivery_time);
        $minimum = now()->addMinutes(30);

        return $scheduled->lt($minimum) ? $minimum : $scheduled;
    }

    private function nextDate(Subscription $subscription): Carbon
    {
        $date = $subscription->next_delivery_date->copy();

        return match ($subscription->frequency) {
            'daily' => $date->addDay(),
            'monthly' => $date->addMonth(),
            default => $date->addWeek(),
        };
    }

    private function orderNumber(): string
    {
        do {
            $number = 'DC-SUB-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
