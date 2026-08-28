<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private readonly NotificationService $notifications) {}

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

        if ($method === 'bank_transfer') {
            app(BankTransferService::class)->createFor($payment);
        }

        return $payment->refresh();
    }

    public function updateMethod(Payment $payment, string $method): Payment
    {
        if (! in_array($method, self::METHODS, true)) {
            throw ValidationException::withMessages(['payment_method' => 'Invalid payment method.']);
        }

        if (in_array($payment->status, ['paid', 'refunded'], true)) {
            throw ValidationException::withMessages(['payment' => 'Completed payments cannot be changed.']);
        }

        return DB::transaction(function () use ($payment, $method) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $payment->update([
                'payment_method' => $method,
                'status' => 'pending',
                'transaction_reference' => null,
                'transaction_id' => null,
                'paid_at' => null,
            ]);

            $payment->order()->update(['payment_status' => 'pending']);

            if ($method === 'wallet') {
                app(WalletService::class)->payForOrder($payment->order, $payment);
            }

            if ($method === 'bank_transfer') {
                app(BankTransferService::class)->createFor($payment);
            }

            return $payment->refresh();
        });
    }

    public function syncPendingOrderAmounts(Payment $payment): Payment
    {
        if (in_array($payment->status, ['paid', 'refunded'], true)) {
            return $payment;
        }

        $payment->loadMissing('order.items');
        $order = $payment->order;

        if (! $order || in_array($order->payment_status, ['paid', 'refunded'], true)) {
            return $payment;
        }

        $deliveryFee = round((float) $order->delivery_fee, 2);
        $serviceCharge = round((float) $order->service_charge, 2);
        $total = round(
            (float) $order->subtotal
            - (float) $order->discount_amount
            - (float) $order->loyalty_discount_amount
            + $deliveryFee
            + $serviceCharge
            + (float) $order->tax_amount,
            2
        );

        if ((float) $order->delivery_fee === $deliveryFee && (float) $order->service_charge === $serviceCharge && (float) $order->total_amount === $total) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $deliveryFee, $serviceCharge, $total) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->with('order')->firstOrFail();
            $order = $payment->order()->lockForUpdate()->firstOrFail();

            if (in_array($payment->status, ['paid', 'refunded'], true) || in_array($order->payment_status, ['paid', 'refunded'], true)) {
                return $payment;
            }

            $order->update([
                'delivery_fee' => $deliveryFee,
                'service_charge' => $serviceCharge,
                'total_amount' => $total,
            ]);

            $payment->update([
                'delivery_fee' => $deliveryFee,
                'service_charge' => $serviceCharge,
                'grand_total' => $total,
                'amount' => $total,
            ]);

            return $payment->refresh();
        });
    }

    public function simulate(Payment $payment, bool $successful): Payment
    {
        if ($payment->status !== 'pending') {
            throw ValidationException::withMessages(['payment' => 'Only pending payments can be processed.']);
        }

        if ($payment->payment_method === 'cash_on_delivery') {
            throw ValidationException::withMessages(['payment_method' => 'Cash on Delivery is paid after delivery is completed.']);
        }

        if ($payment->payment_method === 'bank_transfer') {
            throw ValidationException::withMessages(['payment_method' => 'Bank transfers must be verified by DailyCart after you upload a payment slip.']);
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

        $this->notifyStatus($payment, $successful ? 'paid' : 'failed');

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
        $this->notifyStatus($payment, 'paid');

        return $payment->refresh();
    }

    public function markFailed(Payment $payment): Payment
    {
        $payment->update(['status' => 'failed', 'paid_at' => null]);
        $payment->order()->update(['payment_status' => 'failed']);
        $this->notifyStatus($payment, 'failed');

        return $payment->refresh();
    }

    public function markRefunded(Payment $payment): Payment
    {
        $payment->update(['status' => 'refunded']);
        $payment->order()->update(['payment_status' => 'refunded']);
        $this->notifyStatus($payment, 'refunded');

        return $payment->refresh();
    }

    private function reference(Payment $payment, string $prefix = 'PAY'): string
    {
        return $prefix.'-'.$payment->id.'-'.Str::upper(Str::random(8));
    }

    private function notifyStatus(Payment $payment, string $status): void
    {
        $payment->loadMissing(['order.customer.user', 'order.vendor.user']);
        $order = $payment->order;
        if (! $order) {
            return;
        }

        $labels = [
            'paid' => ['Payment successful', 'Payment for order '.$order->order_number.' was successful.'],
            'failed' => ['Payment failed', 'Payment for order '.$order->order_number.' failed. Please try again.'],
            'refunded' => ['Payment refunded', 'Payment for order '.$order->order_number.' was refunded.'],
        ];
        [$title, $message] = $labels[$status] ?? ['Payment updated', 'Payment for order '.$order->order_number.' is now '.$status.'.'];
        $data = ['order_id' => $order->id, 'payment_id' => $payment->id, 'status' => $status];

        if ($order->customer?->user) {
            $this->notifications->sendOnce(
                $order->customer->user,
                $title,
                $message,
                'payment_'.$status,
                ['database', 'mail', 'push'],
                $data,
                '/order-details/'.$order->id,
                'customer',
                'payment-'.$status.'-customer-'.$payment->id,
            );
        }
        if ($order->vendor?->user) {
            $this->notifications->sendOnce(
                $order->vendor->user,
                $title.': '.$order->order_number,
                $message,
                'payment_'.$status,
                ['database', 'mail', 'push'],
                $data,
                '/vendor-order-details/'.$order->id,
                'vendor',
                'payment-'.$status.'-vendor-'.$payment->id,
            );
        }
        if (in_array($status, ['failed', 'refunded'], true)) {
            $this->notifications->notifyAdmins(
                $title.': '.$order->order_number,
                $message,
                'payment_'.$status,
                ['database', 'mail', 'push'],
                $data,
                '/admin/orders/'.$order->id,
            );
        }
    }
}
