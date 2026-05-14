<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RiderApprovedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Rider account approved')->line('Your rider account has been approved.');
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Rider approved', 'message' => 'Your rider account has been approved.'];
    }
}
