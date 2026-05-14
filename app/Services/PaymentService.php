<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public const METHODS = [
        'cash_on_delivery',
        'card',
        'bank_transfer',
        'wallet',
    ];

    public const STATUSES = [
        'pending',
        'paid',
        'failed',
        'refunded',
    ];

    public function createPlaceholder(Order $order, string $method): Payment
    {
        if (! in_array($method, self::METHODS, true)) {
            throw ValidationException::withMessages(['payment_method' => 'Invalid payment method.']);
        }

        if ($order->payment()->exists()) {
            throw ValidationException::withMessages(['payment' => 'This order already has a payment record.']);
        }

        $payment = $order->payment()->create([
            'payment_method' => $method,
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'delivery_fee' => $order->delivery_fee,
            'service_charge' => $order->service_charge,
            'grand_total' => $order->total_amount,
            'amount' => $order->total_amount,
            'currency' => CurrencyService::CURRENCY,
            'status' => 'pending',
        ]);

        if ($method === 'wallet') {
            app(WalletService::class)->payForOrder($order, $payment);
        }

        return $payment->refresh();
    }

    public function simulate(Payment $payment, bool $successful): Payment
    {
        if ($payment->status !== 'pending') {
            throw ValidationException::withMessages(['payment' => 'Only pending payments can be processed.']);
        }

        if ($payment->payment_method === 'cash_on_delivery') {
            throw ValidationException::withMessages(['payment_method' => 'Cash on Delivery is paid after delivery is completed.']);
        }

        if ($payment->payment_method === 'wallet') {
            throw ValidationException::withMessages(['payment_method' => 'Wallet payments are processed during checkout.']);
        }

        $payment->update([
            'status' => $successful ? 'paid' : 'failed',
            'transaction_reference' => $successful ? $this->reference($payment) : null,
            'transaction_id' => $successful ? $this->reference($payment, 'TXN') : null,
            'paid_at' => $successful ? now() : null,
        ]);

        $payment->order()->update([
            'payment_status' => $successful ? 'paid' : 'failed',
        ]);

        return $payment->refresh();
    }

    public function markPaid(Payment $payment, ?string $reference = null): Payment
    {
        $payment->update([
            'status' => 'paid',
            'transaction_reference' => $reference ?? $this->reference($payment),
            'paid_at' => now(),
        ]);

        $payment->order()->update(['payment_status' => 'paid']);

        return $payment->refresh();
    }

    public function markRefunded(Payment $payment): Payment
    {
        $payment->update(['status' => 'refunded']);
        $payment->order()->update(['payment_status' => 'refunded']);

        return $payment->refresh();
    }

    private function reference(Payment $payment, string $prefix = 'PAY'): string
    {
        return $prefix.'-'.$payment->id.'-'.Str::upper(Str::random(8));
    }
}
