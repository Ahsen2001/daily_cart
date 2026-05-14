<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OutForDeliveryNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Out for Delivery',
            'message' => 'Your order '.$this->order->order_number.' is out for delivery.',
        ];
    }
}
