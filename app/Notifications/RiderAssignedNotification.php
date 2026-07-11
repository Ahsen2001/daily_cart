<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class RiderAssignedNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function toArray(object $notifiable): array
    {
        $this->order->loadMissing('delivery.rider.user');

        $rider = $this->order->delivery?->rider;
        $riderName = $rider?->user?->name;
        $riderPhone = $rider?->user?->phone;
        $customerName = $this->order->customer?->user?->name;
        $customerPhone = $this->order->customer?->phone ?: $this->order->customer?->user?->phone;
        $message = 'A rider has been assigned to order '.$this->order->order_number.'.';

        if ($riderName && $riderPhone) {
            $message = 'Rider '.$riderName.' has been assigned to order '.$this->order->order_number.'. Contact: '.$riderPhone.'.';
        } elseif ($riderName) {
            $message = 'Rider '.$riderName.' has been assigned to order '.$this->order->order_number.'.';
        }

        if ($rider?->user_id === $notifiable->id) {
            $message = 'You have been assigned to order '.$this->order->order_number.'.';

            if ($customerName && $customerPhone) {
                $message = 'You have been assigned to order '.$this->order->order_number.' for '.$customerName.'. Customer contact: '.$customerPhone.'.';
            } elseif ($customerPhone) {
                $message = 'You have been assigned to order '.$this->order->order_number.'. Customer contact: '.$customerPhone.'.';
            }
        }

        return [
            'title' => 'Rider Assigned',
            'message' => $message,
        ];
    }
}
