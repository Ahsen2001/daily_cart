<?php

namespace App\Services;

use App\Jobs\SubscriptionOrderJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionRun;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReliableRecurringOrderService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly PaymentService $payments,
        private readonly SubscriptionService $subscriptions,
        private readonly DeliveryFeeService $deliveryFees,
    ) {}

    public function dispatchDueSubscriptions(): int
    {
        $this->recoverStaleRuns();
        $count = 0;

        Subscription::query()->where('status', 'active')
            ->whereDate('next_delivery_date', '<=', now()->toDateString())
            ->chunkById(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    $date = $subscription->next_delivery_date->toDateString();
                    $run = SubscriptionRun::query()->firstOrCreate(
                        ['run_key' => hash('sha256', $subscription->id.'|'.$date)],
                        [
                            'subscription_id' => $subscription->id, 'scheduled_date' => $date,
                            'status' => 'queued',
                            'max_attempts' => (int) config('recurring_orders.max_attempts', 3),
                            'first_dispatched_at' => now(),
                        ],
                    );

                    if ($run->wasRecentlyCreated) {
                        $this->dispatchRun($run);
                        $count++;
                    } elseif ($run->status === 'retry_scheduled' && $run->next_retry_at?->lte(now())) {
                        $run->update(['status' => 'queued', 'next_retry_at' => null]);
                        $this->dispatchRun($run->refresh());
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function processRun(SubscriptionRun $run): ?Order
    {
        $state = DB::transaction(function () use ($run) {
            $run = SubscriptionRun::query()->with(['subscription', 'order'])->lockForUpdate()->findOrFail($run->id);
            if (! in_array($run->status, ['queued', 'retry_scheduled'], true)) return null;
            if ($run->order) {
                $run->update(['status' => 'succeeded', 'completed_at' => now(), 'next_retry_at' => null]);
                return ['order' => $run->order, 'run' => $run, 'attempt' => null];
            }
            if (! $run->subscription || $run->subscription->status !== 'active') {
                $run->update(['status' => 'skipped', 'completed_at' => now(), 'failure_reason' => 'Subscription is no longer active.']);
                return null;
            }
            if ($run->subscription->next_delivery_date?->toDateString() !== $run->scheduled_date->toDateString()) {
                $run->update(['status' => 'skipped', 'completed_at' => now(), 'failure_reason' => 'Subscription schedule has moved past this run.']);
                return null;
            }
            $attempt = $run->attempt_count + 1;
            $run->update(['status' => 'running', 'attempt_count' => $attempt, 'started_at' => now(), 'next_retry_at' => null]);
            return ['order' => null, 'run' => $run->refresh(), 'attempt' => $attempt];
        });

        if ($state === null) return null;
        if ($state['order']) return $state['order'];

        /** @var SubscriptionRun $run */
        $run = $state['run'];
        $attempt = (int) $state['attempt'];
        $startedAt = now();

        try {
            $order = $this->generate($run->subscription()->firstOrFail(), $run);
            DB::transaction(function () use ($run, $attempt, $startedAt, $order) {
                $locked = SubscriptionRun::query()->lockForUpdate()->findOrFail($run->id);
                $locked->attempts()->firstOrCreate(
                    ['attempt_number' => $attempt],
                    ['status' => 'succeeded', 'started_at' => $startedAt, 'completed_at' => now(), 'metadata' => ['order_id' => $order->id]],
                );
                $locked->update(['status' => 'succeeded', 'completed_at' => now(), 'next_retry_at' => null, 'failure_reason' => null, 'last_error_class' => null]);
            });
            return $order;
        } catch (Throwable $exception) {
            $this->recordFailure($run, $exception, $attempt, $startedAt);
            return null;
        }
    }

    public function generate(Subscription $subscription, SubscriptionRun $run): Order
    {
        if ($subscription->status !== 'active') {
            throw ValidationException::withMessages(['subscription' => 'Only active subscriptions can generate recurring orders.']);
        }

        return DB::transaction(function () use ($subscription, $run) {
            if ($existing = Order::query()->where('subscription_run_id', $run->id)->first()) return $existing;
            $subscription = Subscription::whereKey($subscription->id)->lockForUpdate()
                ->with(['customer.user', 'vendor.user', 'product', 'variant'])->firstOrFail();
            $product = Product::whereKey($subscription->product_id)->lockForUpdate()->first();
            $variant = $subscription->variant;
            $this->subscriptions->ensureProductEligible($product, $subscription->quantity, $variant);
            $scheduledAt = $this->scheduledDateTime($subscription);
            $subtotal = round((float) $subscription->unit_price * $subscription->quantity, 2);
            $deliveryFee = $this->deliveryFees->calculate($subtotal, null, null, (int) $subscription->quantity, $subscription->customer);
            $serviceCharge = OrderService::serviceChargeForSubtotal($subtotal);
            $total = round($subtotal + $deliveryFee + $serviceCharge, 2);
            $order = Order::create([
                'order_number' => $this->orderNumber(), 'customer_id' => $subscription->customer_id,
                'vendor_id' => $subscription->vendor_id, 'subscription_id' => $subscription->id,
                'subscription_run_id' => $run->id, 'subtotal' => $subtotal, 'discount_amount' => 0,
                'loyalty_points_redeemed' => 0, 'loyalty_discount_amount' => 0,
                'delivery_fee' => $deliveryFee, 'service_charge' => $serviceCharge,
                'tax_amount' => 0, 'total_amount' => $total, 'currency' => CurrencyService::CURRENCY,
                'delivery_address' => $subscription->delivery_address, 'order_status' => 'pending',
                'payment_status' => 'pending', 'placed_at' => now(), 'scheduled_delivery_at' => $scheduledAt,
            ]);
            $order->items()->create([
                'product_id' => $product->id, 'product_variant_id' => $variant?->id,
                'vendor_id' => $product->vendor_id,
                'product_name' => $product->name.($variant ? ' - '.$variant->name : ''),
                'quantity' => $subscription->quantity, 'unit_price' => $subscription->unit_price,
                'total_price' => $subtotal,
            ]);
            $this->payments->createPlaceholder($order, $subscription->payment_method);
            $order->delivery()->create(['pickup_address' => $subscription->vendor->address, 'delivery_address' => $subscription->delivery_address, 'scheduled_at' => $scheduledAt, 'status' => 'pending']);
            $subscription->update(['last_generated_at' => now(), 'failed_reason' => null, 'next_delivery_date' => $this->nextDate($subscription)]);
            $this->subscriptions->completeIfEnded($subscription->refresh());
            $data = ['subscription_id' => $subscription->id, 'subscription_run_id' => $run->id, 'order_id' => $order->id];
            $this->notifications->sendOnce($subscription->customer->user, 'Recurring order created', 'Order '.$order->order_number.' was created from your subscription.', 'recurring_order_created', ['database', 'mail', 'push'], $data, '/customer/orders/'.$order->id, 'customer', 'subscription-run-success-customer-'.$run->id);
            $this->notifications->sendOnce($subscription->vendor->user, 'Recurring order created', 'Order '.$order->order_number.' is waiting for confirmation.', 'recurring_order_created', ['database', 'mail', 'push'], $data, '/vendor/orders/'.$order->id, 'vendor', 'subscription-run-success-vendor-'.$run->id);
            return $order->refresh();
        });
    }

    public function retryExhausted(SubscriptionRun $run): SubscriptionRun
    {
        $run = DB::transaction(function () use ($run) {
            $run = SubscriptionRun::query()->lockForUpdate()->findOrFail($run->id);
            if ($run->status !== 'exhausted') throw ValidationException::withMessages(['run' => 'Only exhausted subscription runs can be retried manually.']);
            $run->update(['status' => 'queued', 'max_attempts' => $run->attempt_count + 1, 'next_retry_at' => null, 'failure_reason' => null]);
            return $run->refresh();
        });
        $this->dispatchRun($run);
        return $run;
    }

    public function recoverStaleRuns(): int
    {
        $threshold = now()->subMinutes((int) config('recurring_orders.stale_run_minutes', 15));
        $count = 0;
        SubscriptionRun::query()->whereIn('status', ['queued', 'running'])->where('updated_at', '<=', $threshold)
            ->chunkById(100, function ($runs) use (&$count) {
                foreach ($runs as $run) {
                    $this->recordFailure($run, new \RuntimeException('Subscription run became stale while waiting for a worker.'), max(1, $run->attempt_count), $run->started_at ?? $run->updated_at);
                    $count++;
                }
            });
        return $count;
    }

    private function recordFailure(SubscriptionRun $run, Throwable $exception, int $attempt, Carbon $startedAt): void
    {
        $exhausted = false;
        $run = DB::transaction(function () use ($run, $exception, $attempt, $startedAt, &$exhausted) {
            $run = SubscriptionRun::query()->with('subscription')->lockForUpdate()->findOrFail($run->id);
            if (in_array($run->status, ['succeeded', 'skipped', 'exhausted'], true)) return $run;
            $attempt = max($attempt, $run->attempt_count ?: 1);
            if ($run->attempt_count < $attempt) $run->attempt_count = $attempt;
            $message = Str::limit($exception->getMessage() ?: 'Recurring order generation failed.', 2000);
            $run->attempts()->firstOrCreate(
                ['attempt_number' => $attempt],
                ['status' => 'failed', 'error_class' => $exception::class, 'error_message' => $message, 'started_at' => $startedAt, 'completed_at' => now()],
            );
            $exhausted = $run->attempt_count >= $run->max_attempts;
            $run->fill([
                'status' => $exhausted ? 'exhausted' : 'retry_scheduled',
                'next_retry_at' => $exhausted ? null : now()->addMinutes($this->backoffMinutes($run->attempt_count)),
                'completed_at' => $exhausted ? now() : null,
                'escalated_at' => $exhausted ? now() : $run->escalated_at,
                'failure_reason' => $message, 'last_error_class' => $exception::class,
            ])->save();
            $run->subscription?->update(['failed_reason' => $message]);
            return $run->refresh();
        });
        if ($exhausted) $this->escalate($run);
    }

    private function escalate(SubscriptionRun $run): void
    {
        $run->loadMissing(['subscription.customer.user', 'subscription.vendor.user']);
        $subscription = $run->subscription;
        $message = 'Recurring order for '.$run->scheduled_date->format('Y-m-d').' failed after '.$run->attempt_count.' attempts. '.($run->failure_reason ?? 'Administrator review is required.');
        $data = ['subscription_id' => $subscription?->id, 'subscription_run_id' => $run->id, 'scheduled_date' => $run->scheduled_date->toDateString(), 'attempt_count' => $run->attempt_count, 'status' => 'exhausted'];
        if ($subscription?->customer?->user) $this->notifications->sendOnce($subscription->customer->user, 'Subscription order needs attention', $message, 'subscription_run_exhausted', ['database', 'mail', 'push'], $data, '/customer/subscriptions', 'customer', 'subscription-run-exhausted-customer-'.$run->id);
        if ($subscription?->vendor?->user) $this->notifications->sendOnce($subscription->vendor->user, 'Subscription order failed', $message, 'subscription_run_exhausted', ['database', 'mail', 'push'], $data, '/vendor/subscriptions', 'vendor', 'subscription-run-exhausted-vendor-'.$run->id);
        $this->notifications->notifyAdmins('Subscription retries exhausted', $message, 'subscription_run_escalated', ['database', 'mail', 'push'], $data, '/admin/subscriptions');
    }

    private function dispatchRun(SubscriptionRun $run): void { SubscriptionOrderJob::dispatch($run->id); }

    private function backoffMinutes(int $attempt): int
    {
        $delays = array_values(config('recurring_orders.backoff_minutes', [5, 30, 120]));
        return (int) ($delays[min(max(0, $attempt - 1), count($delays) - 1)] ?? 120);
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
        return match ($subscription->frequency) { 'daily' => $date->addDay(), 'monthly' => $date->addMonth(), default => $date->addWeek() };
    }

    private function orderNumber(): string
    {
        do { $number = 'DC-SUB-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT); }
        while (Order::where('order_number', $number)->exists());
        return $number;
    }
}
