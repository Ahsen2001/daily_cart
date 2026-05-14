<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RiderRejectedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Rider account rejected')->line('Your rider account was rejected.');
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Rider rejected', 'message' => 'Your rider account was rejected.'];
    }
}
