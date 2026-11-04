<?php

namespace App\Jobs;

use App\Models\SubscriptionRun;
use App\Services\ReliableRecurringOrderService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SubscriptionOrderJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 180;

    public int $uniqueFor = 900;

    public function __construct(public readonly int $subscriptionRunId) {}

    public function uniqueId(): string
    {
        return 'subscription-run-'.$this->subscriptionRunId;
    }

    public function handle(ReliableRecurringOrderService $recurringOrders): void
    {
        $run = SubscriptionRun::query()->find($this->subscriptionRunId);
        if ($run) {
            $recurringOrders->processRun($run);
        }
    }
}
