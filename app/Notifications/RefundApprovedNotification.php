<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Notifications\Notification;

class RefundApprovedNotification extends Notification
{
    public function __construct(private readonly Refund $refund) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Refund approved',
            'message' => 'Your refund for order '.$this->refund->order?->order_number.' has been added to your wallet.',
        ];
    }
}
