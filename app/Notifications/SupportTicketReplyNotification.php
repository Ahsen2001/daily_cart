<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketReplyNotification extends Notification
{
    public function __construct(private readonly SupportTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Support ticket reply')->line('A reply was added to ticket: '.$this->ticket->subject);
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Support ticket reply', 'message' => 'A reply was added to ticket: '.$this->ticket->subject];
    }
}
