<?php

return [
    'targets' => [
        'http_p50_ms' => (int) env('LOAD_TARGET_HTTP_P50_MS', 300),
        'http_p95_ms' => (int) env('LOAD_TARGET_HTTP_P95_MS', 800),
        'http_p99_ms' => (int) env('LOAD_TARGET_HTTP_P99_MS', 1500),
        'error_rate' => (float) env('LOAD_TARGET_ERROR_RATE', 0.01),
        'queue_wait_p50_seconds' => (float) env('LOAD_TARGET_QUEUE_WAIT_P50_SECONDS', 2),
        'queue_wait_p95_seconds' => (float) env('LOAD_TARGET_QUEUE_WAIT_P95_SECONDS', 5),
        'queue_wait_p99_seconds' => (float) env('LOAD_TARGET_QUEUE_WAIT_P99_SECONDS', 15),
        'new_failed_jobs' => (int) env('LOAD_TARGET_NEW_FAILED_JOBS', 0),
    ],
];
