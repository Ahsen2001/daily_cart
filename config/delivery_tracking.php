<?php

return [
    'fresh_seconds' => (int) env('DELIVERY_TRACKING_FRESH_SECONDS', 90),
    'outdated_seconds' => (int) env('DELIVERY_TRACKING_OUTDATED_SECONDS', 180),
    'poll_seconds' => (int) env('DELIVERY_TRACKING_POLL_SECONDS', 20),
];
