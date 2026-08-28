<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $title,
        public readonly string $body,
        /** @var array<string, string> */
        public readonly array $details = [],
        public readonly ?string $actionUrl = null,
        public readonly ?string $actionLabel = null,
        public readonly ?string $recipientName = null,
        public readonly string $role = 'customer',
        public readonly string $category = 'Update',
        public readonly string $tone = 'info',
        public readonly ?string $notificationCenterUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifications.generic',
            text: 'emails.notifications.generic-text',
        );
    }
}
