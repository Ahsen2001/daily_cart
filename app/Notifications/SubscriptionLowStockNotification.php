<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Notification;

class SubscriptionLowStockNotification extends Notification
{
    public function __construct(public readonly Subscription $subscription) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Low Stock',
            'message' => 'Stock is low for '.$this->subscription->product?->name.' subscription orders.',
        ];
    }
}
