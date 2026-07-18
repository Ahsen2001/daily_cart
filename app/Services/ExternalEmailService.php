<?php

namespace App\Services;

use App\Mail\DailyCartStatusMail;
use App\Mail\OrderInvoiceMail;
use App\Mail\OtpMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ExternalEmailService
{
    public function welcome(User $user): void
    {
        $this->send($user, 'Welcome to DailyCart', 'Your DailyCart account has been created successfully.');
    }

    public function orderPlaced(Order $order): void
    {
        $this->send($order->customer->user, 'Order placed', 'Your order '.$order->order_number.' has been placed successfully.');
    }

    public function orderStatus(Order $order, string $message): void
    {
        $this->send($order->customer->user, 'Order update: '.$order->order_number, $message);
    }

    /** Send the itemized invoice when the rider starts the final delivery leg. */
    public function outForDeliveryInvoice(Order $order): void
    {
        $order->loadMissing(['customer.user', 'vendor', 'items', 'payment', 'delivery.rider.user']);

        $customer = $order->customer?->user;

        if (! $customer) {
            return;
        }

        Mail::to($customer->email)->queue(
            (new OrderInvoiceMail($order))->afterCommit()
        );
    }

    public function paymentStatus(Payment $payment, string $message): void
    {
        $this->send($payment->order->customer->user, 'Payment update', $message);
    }

    public function refundStatus(Refund $refund, string $message): void
    {
        $this->send($refund->order->customer->user, 'Refund update', $message);
    }

    public function approval(User $user, string $title, string $message): void
    {
        $this->send($user, $title, $message);
    }

    public function otp(string $email, string $code, string $purpose): void
    {
        Mail::to($email)->queue((new OtpMail($code, $purpose))->afterCommit());
    }

    private function send(User $user, string $subject, string $message): void
    {
        Mail::to($user->email)->queue(
            (new DailyCartStatusMail($subject, $user->name, $message))->afterCommit()
        );
    }
}
