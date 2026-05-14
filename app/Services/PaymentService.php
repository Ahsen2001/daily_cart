<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    public function createPlaceholder(Order $order, string $method): Payment
    {
        return $order->payment()->create([
            'payment_method' => $method,
            'amount' => $order->total_amount,
            'currency' => CurrencyService::CURRENCY,
            'status' => 'pending',
        ]);
    }
}
