<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Delivered',
            'message' => 'Your order '.$this->order->order_number.' has been delivered.',
        ];
    }
}
