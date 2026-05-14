<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification
{
    public function __construct(private readonly Product $product) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Low stock alert')->line($this->product->name.' has low stock.');
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Low stock alert', 'message' => $this->product->name.' has low stock.'];
    }
}
