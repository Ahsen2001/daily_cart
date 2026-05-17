<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\RecurringOrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Validation\ValidationException;

class SubscriptionOrderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $subscriptionId) {}

    public function handle(RecurringOrderService $recurringOrders): void
    {
        $subscription = Subscription::with(['customer.user', 'vendor.user', 'product', 'variant'])->find($this->subscriptionId);

        if (! $subscription || $subscription->status !== 'active') {
            return;
        }

        try {
            $recurringOrders->generate($subscription);
        } catch (ValidationException $exception) {
            $recurringOrders->markFailed($subscription, $exception->errors()[array_key_first($exception->errors())][0] ?? 'Recurring order generation failed.');
        }
    }
}
