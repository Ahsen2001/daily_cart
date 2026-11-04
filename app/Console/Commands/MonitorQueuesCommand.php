<?php

namespace App\Console\Commands;

use App\Services\QueueHealthMonitor;
use Illuminate\Console\Command;

class MonitorQueuesCommand extends Command
{
    protected $signature = 'dailycart:monitor-queues {--no-notify}';

    protected $description = 'Monitor queue backlog, failed jobs and stale subscription runs.';

    public function handle(QueueHealthMonitor $monitor): int
    {
        $metrics = $monitor->inspect(! $this->option('no-notify'));
        $this->table(['Metric', 'Value'], collect($metrics)->map(fn ($value, $key) => [$key, is_bool($value) ? ($value ? 'yes' : 'no') : $value])->values()->all());

        return $metrics['healthy'] ? self::SUCCESS : self::FAILURE;
    }
}
