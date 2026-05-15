<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class RecurringOrderCreatedNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Recurring Order Created',
            'message' => 'Recurring order '.$this->order->order_number.' has been created.',
        ];
    }
}
