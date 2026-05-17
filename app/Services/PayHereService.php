<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PayHereService
{
    public function checkoutPayload(Payment $payment): array
    {
        $payment->loadMissing('order.customer.user');
        $order = $payment->order;
        $amount = $this->formatAmount((float) $payment->amount);

        if ($payment->currency !== CurrencyService::CURRENCY) {
            throw ValidationException::withMessages(['currency' => 'PayHere payments must use LKR.']);
        }

        if (blank(config('services.payhere.merchant_id')) || blank(config('services.payhere.merchant_secret'))) {
            throw ValidationException::withMessages(['payhere' => 'PayHere merchant credentials are not configured.']);
        }

        $payload = [
            'merchant_id' => config('services.payhere.merchant_id'),
            'return_url' => route('customer.payments.success', $payment),
            'cancel_url' => route('customer.payments.failed', $payment),
            'notify_url' => route('payhere.notify'),
            'order_id' => $order->order_number,
            'items' => 'DailyCart order '.$order->order_number,
            'currency' => CurrencyService::CURRENCY,
            'amount' => $amount,
            'first_name' => $order->customer?->first_name ?: $order->customer?->user?->name,
            'last_name' => $order->customer?->last_name ?: 'Customer',
            'email' => $order->customer?->user?->email,
            'phone' => $order->customer?->phone ?: $order->customer?->user?->phone,
            'address' => $order->delivery_address,
            'city' => $order->customer?->city ?: 'Colombo',
            'country' => 'Sri Lanka',
        ];

        $payload['hash'] = $this->checkoutHash((string) $payload['merchant_id'], $payload['order_id'], $amount, CurrencyService::CURRENCY);

        PaymentGatewayTransaction::updateOrCreate(
            ['payment_id' => $payment->id, 'gateway' => 'payhere', 'gateway_order_id' => $order->order_number],
            [
                'status' => 'pending',
                'amount' => $payment->amount,
                'currency' => CurrencyService::CURRENCY,
                'request_payload' => Arr::except($payload, ['hash']),
            ]
        );

        return $payload;
    }

    public function checkoutUrl(): string
    {
        return config('services.payhere.sandbox')
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout';
    }

    public function verifyNotification(array $payload): bool
    {
        $localSignature = strtoupper(md5(
            ($payload['merchant_id'] ?? '').
            ($payload['order_id'] ?? '').
            ($payload['payhere_amount'] ?? '').
            ($payload['payhere_currency'] ?? '').
            ($payload['status_code'] ?? '').
            strtoupper(md5((string) config('services.payhere.merchant_secret')))
        ));

        return hash_equals($localSignature, strtoupper((string) ($payload['md5sig'] ?? '')));
    }

    public function recordNotification(Payment $payment, array $payload): PaymentGatewayTransaction
    {
        return PaymentGatewayTransaction::updateOrCreate(
            ['payment_id' => $payment->id, 'gateway' => 'payhere', 'gateway_order_id' => $payload['order_id'] ?? $payment->order?->order_number],
            [
                'gateway_payment_id' => $payload['payment_id'] ?? null,
                'status' => ((string) ($payload['status_code'] ?? '') === '2') ? 'paid' : 'failed',
                'amount' => (float) ($payload['payhere_amount'] ?? $payment->amount),
                'currency' => $payload['payhere_currency'] ?? CurrencyService::CURRENCY,
                'response_payload' => $payload,
                'paid_at' => ((string) ($payload['status_code'] ?? '') === '2') ? now() : null,
            ]
        );
    }

    private function checkoutHash(string $merchantId, string $orderId, string $amount, string $currency): string
    {
        return strtoupper(md5($merchantId.$orderId.$amount.$currency.strtoupper(md5((string) config('services.payhere.merchant_secret')))));
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
