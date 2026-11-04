<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('dailycart:process-subscriptions')->dailyAt('00:10');
Schedule::command('sanctum:prune-expired --hours=24')->dailyAt('01:00');
Schedule::call(fn () => app(\App\Services\DeliveryRecoveryService::class)->escalateOverdue())
    ->name('delivery-recovery-escalation')->everyFiveMinutes()->withoutOverlapping();
