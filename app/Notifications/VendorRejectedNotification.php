<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorRejectedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Vendor account rejected')->line('Your vendor account was rejected.');
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Vendor rejected', 'message' => 'Your vendor account was rejected.'];
    }
}
