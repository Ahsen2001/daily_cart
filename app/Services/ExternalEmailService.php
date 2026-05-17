<?php

namespace App\Services;

use App\Mail\DailyCartStatusMail;
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
        Mail::to($email)->send(new OtpMail($code, $purpose));
    }

    private function send(User $user, string $subject, string $message): void
    {
        Mail::to($user->email)->send(new DailyCartStatusMail($subject, $user->name, $message));
    }
}
