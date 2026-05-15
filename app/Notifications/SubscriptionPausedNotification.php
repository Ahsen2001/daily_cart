<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Notification;

class SubscriptionPausedNotification extends Notification
{
    public function __construct(public readonly Subscription $subscription) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Paused',
            'message' => 'Your subscription has been paused.',
        ];
    }
}
