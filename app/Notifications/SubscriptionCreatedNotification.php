<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Notification;

class SubscriptionCreatedNotification extends Notification
{
    public function __construct(public readonly Subscription $subscription) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Created',
            'message' => 'Your subscription for '.$this->subscription->product?->name.' is active.',
        ];
    }
}
