<?php

namespace App\Console\Commands;

use App\Services\QueueHealthMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonitorLoadTestQueuesCommand extends Command
{
    protected $signature = 'dailycart:load-queue-metrics
        {--duration=300 : Sampling duration in seconds}
        {--interval=2 : Seconds between samples}
        {--output= : Optional JSON summary path}';

    protected $description = 'Measure queue wait percentiles and new failed jobs during a staging load test.';

    public function handle(QueueHealthMonitor $monitor): int
    {
        if (app()->environment('production')) {
            $this->error('Load queue monitoring is disabled in production.');

            return self::FAILURE;
        }

        $duration = max(1, min(86400, (int) $this->option('duration')));
        $interval = max(1, min(60, (int) $this->option('interval')));
        $baselineFailed = $this->failedJobCount();
        $samples = [];
        $deadline = microtime(true) + $duration;

        $this->info("Sampling queue health for {$duration} seconds every {$interval} second(s).");

        do {
            $metrics = $monitor->inspect(false);
            $samples[] = (float) $metrics['oldest_job_age_seconds'];
            sleep($interval);
        } while (microtime(true) < $deadline);

        $newFailedJobs = max(0, $this->failedJobCount() - $baselineFailed);
        $summary = [
            'sample_count' => count($samples),
            'queue_wait_seconds' => [
                'p50' => $this->percentile($samples, 50),
                'p95' => $this->percentile($samples, 95),
                'p99' => $this->percentile($samples, 99),
                'max' => $samples === [] ? 0 : max($samples),
            ],
            'new_failed_jobs' => $newFailedJobs,
            'targets' => [
                'p50' => (float) config('load_testing.targets.queue_wait_p50_seconds'),
                'p95' => (float) config('load_testing.targets.queue_wait_p95_seconds'),
                'p99' => (float) config('load_testing.targets.queue_wait_p99_seconds'),
                'new_failed_jobs' => (int) config('load_testing.targets.new_failed_jobs'),
            ],
        ];
        $summary['passed'] = $summary['queue_wait_seconds']['p50'] <= $summary['targets']['p50']
            && $summary['queue_wait_seconds']['p95'] <= $summary['targets']['p95']
            && $summary['queue_wait_seconds']['p99'] <= $summary['targets']['p99']
            && $newFailedJobs <= $summary['targets']['new_failed_jobs'];

        $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($output = $this->option('output')) {
            $path = $this->outputPath((string) $output);
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
            $this->info("Queue metrics written to {$path}");
        }

        return $summary['passed'] ? self::SUCCESS : self::FAILURE;
    }

    /** @param array<int, float> $values */
    private function percentile(array $values, int $percentile): float
    {
        if ($values === []) {
            return 0;
        }

        sort($values, SORT_NUMERIC);
        $index = (int) ceil(($percentile / 100) * count($values)) - 1;

        return round($values[max(0, min(count($values) - 1, $index))], 3);
    }

    private function failedJobCount(): int
    {
        return Schema::hasTable('failed_jobs') ? (int) DB::table('failed_jobs')->count() : 0;
    }

    private function outputPath(string $output): string
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $output) || str_starts_with($output, DIRECTORY_SEPARATOR)) {
            return $output;
        }

        return base_path($output);
    }
}
