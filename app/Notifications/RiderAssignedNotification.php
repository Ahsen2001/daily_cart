<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class RiderAssignedNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Rider Assigned',
            'message' => 'A rider has been assigned to order '.$this->order->order_number.'.',
        ];
    }
}
