<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApprovedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Vendor account approved')->line('Your vendor account has been approved.');
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Vendor approved', 'message' => 'Your vendor account has been approved.'];
    }
}
