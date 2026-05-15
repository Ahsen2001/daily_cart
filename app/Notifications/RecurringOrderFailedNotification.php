<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Notification;

class RecurringOrderFailedNotification extends Notification
{
    public function __construct(public readonly Subscription $subscription, public readonly string $reason) {}

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Recurring Order Failed',
            'message' => $this->reason,
        ];
    }
}
