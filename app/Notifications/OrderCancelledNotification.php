<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Cancelled',
            'message' => 'Order '.$this->order->order_number.' has been cancelled.',
        ];
    }
}
