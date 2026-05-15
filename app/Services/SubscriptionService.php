<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function create(Customer $customer, array $data): Subscription
    {
        $product = Product::with(['vendor', 'category'])->findOrFail($data['product_id']);
        $this->ensureProductEligible($product, (int) $data['quantity']);

        return DB::transaction(function () use ($customer, $data, $product) {
            $unitPrice = (float) ($product->discount_price ?: $product->price);
            $total = round($unitPrice * (int) $data['quantity'], 2);
            $startDate = Carbon::parse($data['start_date']);
            $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'frequency' => $data['frequency'],
                'quantity' => (int) $data['quantity'],
                'unit_price' => $unitPrice,
                'total_amount' => $total,
                'delivery_address' => $data['delivery_address'],
                'preferred_delivery_time' => $data['preferred_delivery_time'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'next_delivery_date' => $startDate,
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
                'plan_name' => $product->name.' '.$data['frequency'].' subscription',
                'price' => $total,
                'currency' => CurrencyService::CURRENCY,
                'starts_at' => $startDate,
                'ends_at' => $endDate ?? $startDate->copy()->addYears(10),
                'status' => 'active',
            ]);

            $this->notifications->send($customer->user, 'Subscription created', 'Your '.$product->name.' subscription is active.', 'subscription_created');
            $this->notifications->send($product->vendor->user, 'New subscription', $customer->user->name.' subscribed to '.$product->name.'.', 'new_subscription');

            return $subscription->refresh();
        });
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $this->ensureProductEligible($subscription->product, (int) $data['quantity']);

        return DB::transaction(function () use ($subscription, $data) {
            $unitPrice = (float) ($subscription->product->discount_price ?: $subscription->product->price);
            $total = round($unitPrice * (int) $data['quantity'], 2);

            $subscription->update([
                'quantity' => (int) $data['quantity'],
                'unit_price' => $unitPrice,
                'total_amount' => $total,
                'price' => $total,
                'delivery_address' => $data['delivery_address'],
                'preferred_delivery_time' => $data['preferred_delivery_time'],
                'end_date' => $data['end_date'] ?? null,
                'ends_at' => isset($data['end_date']) ? Carbon::parse($data['end_date']) : $subscription->starts_at->copy()->addYears(10),
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);

            return $subscription->refresh();
        });
    }

    public function pause(Subscription $subscription): Subscription
    {
        return $this->changeStatus($subscription, 'paused', 'Subscription paused', 'Your subscription has been paused.', 'subscription_paused');
    }

    public function resume(Subscription $subscription): Subscription
    {
        $this->ensureProductEligible($subscription->product, $subscription->quantity);

        return $this->changeStatus($subscription, 'active', 'Subscription resumed', 'Your subscription has been resumed.', 'subscription_resumed');
    }

    public function cancel(Subscription $subscription): Subscription
    {
        return $this->changeStatus($subscription, 'cancelled', 'Subscription cancelled', 'Your subscription has been cancelled.', 'subscription_cancelled');
    }

    public function completeIfEnded(Subscription $subscription): ?Subscription
    {
        if ($subscription->end_date && $subscription->next_delivery_date && $subscription->next_delivery_date->gt($subscription->end_date)) {
            $subscription->update(['status' => 'completed']);

            return $subscription->refresh();
        }

        return null;
    }

    public function ensureProductEligible(?Product $product, int $quantity): void
    {
        if (! $product || $product->status !== 'approved' || ! $product->is_subscription_eligible) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is not eligible for subscriptions.',
            ]);
        }

        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'This product does not have enough stock for the subscription quantity.',
            ]);
        }
    }

    private function changeStatus(Subscription $subscription, string $status, string $title, string $message, string $type): Subscription
    {
        return DB::transaction(function () use ($subscription, $status, $title, $message, $type) {
            $subscription->update(['status' => $status]);
            $this->notifications->send($subscription->customer->user, $title, $message, $type);

            return $subscription->refresh();
        });
    }
}
