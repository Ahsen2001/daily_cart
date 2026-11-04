<?php

namespace App\Services;

use App\Models\SubscriptionRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueueHealthMonitor
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** @return array{queued_jobs:int,oldest_job_age_minutes:int,failed_jobs_24h:int,stale_subscription_runs:int,healthy:bool} */
    public function inspect(bool $notify = true): array
    {
        $queued = Schema::hasTable('jobs') ? (int) DB::table('jobs')->count() : 0;
        $oldestAvailable = Schema::hasTable('jobs') ? DB::table('jobs')->min('available_at') : null;
        $oldestAge = $oldestAvailable ? max(0, (int) floor((time() - (int) $oldestAvailable) / 60)) : 0;
        $failed = Schema::hasTable('failed_jobs')
            ? (int) DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count()
            : 0;
        $staleThreshold = now()->subMinutes((int) config('recurring_orders.stale_run_minutes', 15));
        $staleRuns = Schema::hasTable('subscription_runs')
            ? SubscriptionRun::query()->whereIn('status', ['queued', 'running'])->where('updated_at', '<=', $staleThreshold)->count()
            : 0;
        $healthy = $queued < (int) config('recurring_orders.queue_warning_size', 100)
            && $oldestAge < (int) config('recurring_orders.queue_warning_age_minutes', 10)
            && $failed < (int) config('recurring_orders.failed_jobs_warning_count', 1)
            && $staleRuns === 0;
        $metrics = [
            'queued_jobs' => $queued, 'oldest_job_age_minutes' => $oldestAge,
            'failed_jobs_24h' => $failed, 'stale_subscription_runs' => $staleRuns,
            'healthy' => $healthy,
        ];

        if ($notify && ! $healthy) {
            $message = "Queue warning: {$queued} queued, oldest {$oldestAge} minute(s), {$failed} failed in 24h, {$staleRuns} stale subscription run(s).";
            User::role(['Admin', 'Super Admin'])->each(function (User $admin) use ($message, $metrics) {
                $this->notifications->sendOnce(
                    $admin, 'DailyCart queue health warning', $message, 'queue_health_warning',
                    ['database', 'mail', 'push'], $metrics, '/admin/subscriptions', null,
                    'queue-health-'.$admin->id.'-'.now()->format('YmdH'),
                );
            });
        }

        return $metrics;
    }
}
