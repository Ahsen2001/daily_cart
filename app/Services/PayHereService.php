<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentGatewayCallbackEvent;
use App\Models\PaymentGatewayTransaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayHereService
{
    public function checkoutPayload(
        Payment $payment,
        ?string $returnUrl = null,
        ?string $cancelUrl = null
    ): array {
        $payment->loadMissing('order.customer.user');
        $order = $payment->order;
        $amount = $this->formatAmount((float) $payment->amount);

        if ($payment->currency !== CurrencyService::CURRENCY) {
            throw ValidationException::withMessages(['currency' => 'PayHere payments must use LKR.']);
        }

        if (blank(config('payhere.merchant_id')) || blank(config('payhere.merchant_secret'))) {
            throw ValidationException::withMessages(['payhere' => 'PayHere merchant credentials are not configured.']);
        }

        $payload = [
            'merchant_id' => config('payhere.merchant_id'),
            'return_url' => $returnUrl ?? route('customer.payments.success', $payment),
            'cancel_url' => $cancelUrl ?? route('customer.payments.failed', $payment),
            'notify_url' => config('payhere.notify_url') ?: route('payhere.notify'),
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
        return config('payhere.checkout_url');
    }

    public function verifyNotification(array $payload): bool
    {
        if (blank(config('payhere.merchant_secret')) || blank($payload['md5sig'] ?? null)) {
            return false;
        }

        $localSignature = strtoupper(md5(
            ($payload['merchant_id'] ?? '').
            ($payload['order_id'] ?? '').
            ($payload['payhere_amount'] ?? '').
            ($payload['payhere_currency'] ?? '').
            ($payload['status_code'] ?? '').
            strtoupper(md5((string) config('payhere.merchant_secret')))
        ));

        return hash_equals($localSignature, strtoupper((string) ($payload['md5sig'] ?? '')));
    }

    public function processNotification(array $payload, PaymentService $payments): string
    {
        $eventKey = $this->eventKey($payload);
        $normalizedStatus = $this->normalizedStatus($payload['status_code'] ?? null);

        if (! $this->verifyNotification($payload)) {
            $this->recordEvent($eventKey, $payload, null, null, $normalizedStatus, false, false, 'rejected', 'Invalid callback signature.');

            return 'INVALID_SIGNATURE';
        }

        $transaction = PaymentGatewayTransaction::query()
            ->where('gateway', 'payhere')
            ->where('gateway_order_id', (string) ($payload['order_id'] ?? ''))
            ->first();

        if (! $transaction) {
            $this->recordEvent($eventKey, $payload, null, null, $normalizedStatus, true, false, 'order_not_found', 'Gateway order identity was not found.');

            return 'ORDER_NOT_FOUND';
        }

        return DB::transaction(function () use ($eventKey, $payload, $payments, $transaction) {
            $transaction = PaymentGatewayTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();
            $payment = Payment::query()->with('order')->whereKey($transaction->payment_id)->lockForUpdate()->firstOrFail();
            $normalizedStatus = $this->normalizedStatus($payload['status_code'] ?? null);
            $identityError = $this->identityError($transaction, $payment, $payload);

            if ($identityError !== null) {
                $this->recordEvent($eventKey, $payload, $transaction, $payment, $normalizedStatus, true, false, 'rejected', $identityError);

                return 'INVALID_IDENTITY';
            }

            if ($normalizedStatus === null) {
                $this->recordEvent($eventKey, $payload, $transaction, $payment, null, true, true, 'rejected', 'Unsupported PayHere status code.');

                return 'INVALID_STATUS';
            }

            $processingResult = $this->processingResult($payments, $payment->status, $normalizedStatus);
            $event = $this->recordEvent(
                $eventKey,
                $payload,
                $transaction,
                $payment,
                $normalizedStatus,
                true,
                true,
                $processingResult,
            );

            if (! $event->wasRecentlyCreated) {
                return 'OK';
            }

            if ($processingResult === 'applied') {
                $payment = match ($normalizedStatus) {
                    'paid' => $payments->markPaid($payment, (string) $payload['payment_id']),
                    'failed' => $payments->markFailed($payment),
                    'refunded' => $payments->markRefunded($payment),
                    default => $payment,
                };
            }

            if (in_array($processingResult, ['applied', 'idempotent', 'observed'], true)) {
                $transaction->update([
                    'gateway_payment_id' => (string) $payload['payment_id'],
                    'status' => $normalizedStatus,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'response_payload' => $payload,
                    'paid_at' => $normalizedStatus === 'paid'
                        ? ($transaction->paid_at ?? now())
                        : $transaction->paid_at,
                ]);
            }

            return 'OK';
        });
    }

    private function identityError(
        PaymentGatewayTransaction $transaction,
        Payment $payment,
        array $payload,
    ): ?string {
        $merchantId = (string) ($payload['merchant_id'] ?? '');
        $configuredMerchantId = (string) config('payhere.merchant_id');
        $storedMerchantId = (string) data_get($transaction->request_payload, 'merchant_id', $configuredMerchantId);
        if ($merchantId === '' || ! hash_equals($configuredMerchantId, $merchantId) || ! hash_equals($storedMerchantId, $merchantId)) {
            return 'Merchant identity does not match the configured checkout.';
        }

        if ((string) ($payload['order_id'] ?? '') !== (string) $transaction->gateway_order_id) {
            return 'Gateway order identity does not match the stored transaction.';
        }

        if (! $payment->order || $payment->payment_method !== 'card') {
            return 'The stored payment is not an eligible PayHere card payment.';
        }

        $gatewayPaymentId = trim((string) ($payload['payment_id'] ?? ''));
        if ($gatewayPaymentId === '') {
            return 'Gateway payment identity is required.';
        }
        if (filled($transaction->gateway_payment_id)
            && ! hash_equals((string) $transaction->gateway_payment_id, $gatewayPaymentId)) {
            return 'Gateway payment identity does not match the stored transaction.';
        }
        if (PaymentGatewayTransaction::query()
            ->where('gateway', 'payhere')
            ->where('gateway_payment_id', $gatewayPaymentId)
            ->where('id', '!=', $transaction->id)
            ->exists()) {
            return 'Gateway payment identity is already assigned to another transaction.';
        }

        $currency = strtoupper(trim((string) ($payload['payhere_currency'] ?? '')));
        if ($currency !== strtoupper((string) $payment->currency) || $currency !== CurrencyService::CURRENCY) {
            return 'Callback currency does not match the stored payment.';
        }

        $amount = $payload['payhere_amount'] ?? null;
        if (! is_numeric($amount)
            || $this->formatAmount((float) $amount) !== $this->formatAmount((float) $payment->amount)) {
            return 'Callback amount does not match the stored payment.';
        }

        return null;
    }

    private function processingResult(PaymentService $payments, string $current, string $target): string
    {
        if ($target === 'pending') {
            return $current === 'pending' ? 'observed' : 'ignored_transition';
        }
        if ($current === $target) {
            return 'idempotent';
        }

        return $payments->canTransition($current, $target) ? 'applied' : 'ignored_transition';
    }

    private function normalizedStatus(mixed $statusCode): ?string
    {
        return match ((string) $statusCode) {
            '2' => 'paid',
            '0' => 'pending',
            '-1', '-2' => 'failed',
            '-3' => 'refunded',
            default => null,
        };
    }

    private function eventKey(array $payload): string
    {
        return hash('sha256', json_encode([
            'gateway' => 'payhere',
            'merchant_id' => (string) ($payload['merchant_id'] ?? ''),
            'order_id' => (string) ($payload['order_id'] ?? ''),
            'payment_id' => (string) ($payload['payment_id'] ?? ''),
            'status_code' => (string) ($payload['status_code'] ?? ''),
            'amount' => (string) ($payload['payhere_amount'] ?? ''),
            'currency' => (string) ($payload['payhere_currency'] ?? ''),
            'signature' => strtoupper((string) ($payload['md5sig'] ?? '')),
        ], JSON_THROW_ON_ERROR));
    }

    private function recordEvent(
        string $eventKey,
        array $payload,
        ?PaymentGatewayTransaction $transaction,
        ?Payment $payment,
        ?string $normalizedStatus,
        bool $signatureValid,
        bool $identityValid,
        string $processingResult,
        ?string $rejectionReason = null,
    ): PaymentGatewayCallbackEvent {
        $amount = $payload['payhere_amount'] ?? null;

        return PaymentGatewayCallbackEvent::query()->firstOrCreate(
            ['event_key' => $eventKey],
            [
                'payment_gateway_transaction_id' => $transaction?->id,
                'payment_id' => $payment?->id,
                'gateway' => 'payhere',
                'gateway_order_id' => $payload['order_id'] ?? null,
                'gateway_payment_id' => $payload['payment_id'] ?? null,
                'merchant_id' => $payload['merchant_id'] ?? null,
                'status_code' => isset($payload['status_code']) ? (string) $payload['status_code'] : null,
                'normalized_status' => $normalizedStatus,
                'amount' => is_numeric($amount) ? (float) $amount : null,
                'currency' => isset($payload['payhere_currency'])
                    ? strtoupper((string) $payload['payhere_currency'])
                    : null,
                'signature_valid' => $signatureValid,
                'identity_valid' => $identityValid,
                'processing_result' => $processingResult,
                'rejection_reason' => $rejectionReason,
                'payload' => $payload,
                'received_at' => now(),
            ],
        );
    }

    private function checkoutHash(string $merchantId, string $orderId, string $amount, string $currency): string
    {
        return strtoupper(md5($merchantId.$orderId.$amount.$currency.strtoupper(md5((string) config('payhere.merchant_secret')))));
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
