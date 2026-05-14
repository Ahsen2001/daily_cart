<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Notifications\Notification;

class RefundRejectedNotification extends Notification
{
    public function __construct(private readonly Refund $refund) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Refund rejected',
            'message' => 'Your refund request for order '.$this->refund->order?->order_number.' was rejected.',
        ];
    }
}
