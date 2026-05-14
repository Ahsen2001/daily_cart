<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Notifications\Notification;

class RefundRequestedNotification extends Notification
{
    public function __construct(private readonly Refund $refund) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Refund requested',
            'message' => 'Refund request for order '.$this->refund->order?->order_number.' has been submitted.',
        ];
    }
}
