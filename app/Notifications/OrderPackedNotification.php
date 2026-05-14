<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderPackedNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Packed',
            'message' => 'Your order '.$this->order->order_number.' has been packed and is waiting for rider assignment.',
        ];
    }
}
