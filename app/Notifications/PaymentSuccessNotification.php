<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification
{
    public function __construct(private readonly Payment $payment) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment successful',
            'message' => 'Your payment for order '.$this->payment->order?->order_number.' has been marked as paid.',
        ];
    }
}
