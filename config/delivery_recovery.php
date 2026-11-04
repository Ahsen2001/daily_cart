<?php

return [
    'escalation_sla_minutes' => (int) env('DELIVERY_RECOVERY_SLA_MINUTES', 30),

    'reason_categories' => [
        'customer_unavailable' => 'Customer unavailable',
        'incorrect_address' => 'Incorrect or incomplete address',
        'customer_refused' => 'Customer refused delivery',
        'payment_issue' => 'Payment issue',
        'vehicle_breakdown' => 'Vehicle breakdown',
        'rider_incident' => 'Rider incident',
        'unsafe_conditions' => 'Unsafe conditions',
        'damaged_items' => 'Damaged items',
        'vendor_issue' => 'Vendor issue',
        'other' => 'Other',
    ],
];
