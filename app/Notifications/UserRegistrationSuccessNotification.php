<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegistrationSuccessNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('DailyCart registration successful')
            ->line('Your DailyCart account has been created successfully.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Registration successful',
            'message' => 'Your DailyCart account has been created successfully.',
        ];
    }
}
