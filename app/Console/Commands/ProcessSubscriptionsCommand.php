<?php

namespace App\Console\Commands;

use App\Services\RecurringOrderService;
use Illuminate\Console\Command;

class ProcessSubscriptionsCommand extends Command
{
    protected $signature = 'dailycart:process-subscriptions';

    protected $description = 'Dispatch due DailyCart subscription recurring orders.';

    public function handle(RecurringOrderService $recurringOrders): int
    {
        $count = $recurringOrders->dispatchDueSubscriptions();

        $this->info("Dispatched {$count} subscription order job(s).");

        return self::SUCCESS;
    }
}
