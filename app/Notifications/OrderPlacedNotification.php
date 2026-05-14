<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    public function __construct(private readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Order placed')->line('Order '.$this->order->order_number.' has been placed.');
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Order placed', 'message' => 'Order '.$this->order->order_number.' has been placed.'];
    }
}
