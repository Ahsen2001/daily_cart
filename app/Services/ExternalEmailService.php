<?php

namespace App\Services;

use App\Mail\DailyCartStatusMail;
use App\Mail\OrderInvoiceMail;
use App\Mail\OtpMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ExternalEmailService
{
    public function welcome(User $user): void
    {
        $this->send($user, 'Welcome to DailyCart', 'Your DailyCart account has been created successfully.', route('dashboard'), 'Open your dashboard');
    }

    public function orderPlaced(Order $order): void
    {
        $this->send($order->customer->user, 'Order placed', 'Your order '.$order->order_number.' has been placed successfully.', route('customer.orders.show', $order), 'Track your order');
    }

    public function orderStatus(Order $order, string $message): void
    {
        $this->send($order->customer->user, 'Order update: '.$order->order_number, $message, route('customer.orders.show', $order), 'View order update');
    }

    /** Send the itemized invoice when the rider starts the final delivery leg. */
    public function outForDeliveryInvoice(Order $order): void
    {
        $order->loadMissing(['customer.user', 'vendor', 'items', 'payment', 'delivery.rider.user']);

        $customer = $order->customer?->user;

        if (! $customer) {
            return;
        }

        $this->queue(
            $customer->email,
            (new OrderInvoiceMail($order))->afterCommit(),
            'out-for-delivery invoice',
        );
    }

    public function paymentStatus(Payment $payment, string $message): void
    {
        $this->send($payment->order->customer->user, 'Payment update', $message, route('customer.orders.show', $payment->order), 'View payment details');
    }

    public function refundStatus(Refund $refund, string $message): void
    {
        $this->send($refund->order->customer->user, 'Refund update', $message, route('customer.refunds.index'), 'Review refund');
    }

    public function approval(User $user, string $title, string $message): void
    {
        $this->send($user, $title, $message, route('dashboard'), 'Open DailyCart');
    }

    public function otp(string $email, string $code, string $purpose): void
    {
        $this->queue($email, (new OtpMail($code, $purpose))->afterCommit(), 'OTP');
    }

    private function send(User $user, string $subject, string $message, ?string $actionUrl = null, ?string $actionLabel = null): void
    {
        $this->queue(
            $user->email,
            (new DailyCartStatusMail(
                $subject,
                $user->name,
                $message,
                strtolower((string) ($user->role?->name ?? 'customer')),
                $actionUrl,
                $actionLabel,
            ))->afterCommit(),
            $subject,
        );
    }

    /**
     * Mail transport failures must never roll back a registration, payment, or order action.
     */
    private function queue(string $email, Mailable $mailable, string $context): void
    {
        try {
            Mail::to($email)->queue($mailable);
        } catch (Throwable $exception) {
            Log::error('Unable to queue DailyCart email.', [
                'context' => $context,
                'recipient' => $email,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
