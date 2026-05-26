<?php

return [
    'mode' => env('PAYHERE_MODE', 'sandbox'),
    'merchant_id' => env('PAYHERE_MERCHANT_ID'),
    'merchant_secret' => env('PAYHERE_MERCHANT_SECRET'),
    'app_id' => env('PAYHERE_APP_ID'),
    'app_secret' => env('PAYHERE_APP_SECRET'),
    'currency' => env('PAYHERE_CURRENCY', 'LKR'),

    'checkout_url' => env('PAYHERE_MODE') === 'live'
        ? 'https://www.payhere.lk/pay/checkout'
        : 'https://sandbox.payhere.lk/pay/checkout',
];
