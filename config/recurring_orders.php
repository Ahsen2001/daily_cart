<?php

return [
    'max_attempts' => max(1, (int) env('SUBSCRIPTION_MAX_ATTEMPTS', 3)),
    'backoff_minutes' => [5, 30, 120],
    'stale_run_minutes' => max(5, (int) env('SUBSCRIPTION_STALE_RUN_MINUTES', 15)),
    'queue_warning_size' => max(1, (int) env('QUEUE_WARNING_SIZE', 100)),
    'queue_warning_age_minutes' => max(1, (int) env('QUEUE_WARNING_AGE_MINUTES', 10)),
    'failed_jobs_warning_count' => max(1, (int) env('FAILED_JOBS_WARNING_COUNT', 1)),
];
