<?php

namespace App\Services;

class CurrencyService
{
    public const CURRENCY = 'LKR';

    public static function formatLkr(float|int|string $amount): string
    {
        return 'Rs. '.number_format((float) $amount, 2);
    }
}
