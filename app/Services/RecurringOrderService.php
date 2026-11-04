<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionRun;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecurringOrderService
{
    public function dispatchDueSubscriptions(): int
    {
        return app(ReliableRecurringOrderService::class)->dispatchDueSubscriptions();
    }

    public function generate(Subscription $subscription): Order
    {
        $date = $subscription->next_delivery_date?->toDateString();
        if (! $date) {
            throw ValidationException::withMessages(['subscription' => 'The subscription has no next delivery date.']);
        }
        $run = SubscriptionRun::query()->firstOrCreate(
            ['run_key' => hash('sha256', $subscription->id.'|'.$date)],
            [
                'subscription_id' => $subscription->id, 'scheduled_date' => $date,
                'status' => 'queued', 'max_attempts' => (int) config('recurring_orders.max_attempts', 3),
                'first_dispatched_at' => now(),
            ],
        );
        $order = app(ReliableRecurringOrderService::class)->processRun($run);
        if (! $order) {
            throw ValidationException::withMessages(['subscription' => $run->refresh()->failure_reason ?? 'Recurring order generation did not complete.']);
        }

        return $order;

    }

    public function markFailed(Subscription $subscription, string $reason): void
    {
        $subscription->update(['failed_reason' => Str::limit($reason, 1000)]);
    }
}
