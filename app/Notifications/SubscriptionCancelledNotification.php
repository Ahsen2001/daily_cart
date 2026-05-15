<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Notification;

class SubscriptionCancelledNotification extends Notification
{
    public function __construct(public readonly Subscription $subscription) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Cancelled',
            'message' => 'Your subscription has been cancelled.',
        ];
    }
}
