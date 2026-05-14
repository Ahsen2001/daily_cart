<?php

namespace App\Services;

use App\Mail\GenericNotificationMail;
use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send(User $user, string $title, string $message, string $type, array $channels = ['database']): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);

        if (in_array('mail', $channels, true)) {
            Mail::to($user->email)->send(new GenericNotificationMail($title, $message));
        }

        if (in_array('sms', $channels, true)) {
            $this->sendSmsPlaceholder($user, $message);
        }

        if (in_array('whatsapp', $channels, true)) {
            $this->sendWhatsAppPlaceholder($user, $message);
        }

        if (in_array('push', $channels, true)) {
            $this->sendPushPlaceholder($user, $title, $message);
        }

        return $notification;
    }

    public function notifyAdmins(string $title, string $message, string $type): void
    {
        $this->adminUsers()->each(fn (User $user) => $this->send($user, $title, $message, $type));
    }

    public function lowStockAlert(Product $product, int $threshold = 5): ?Notification
    {
        if ($product->stock_quantity > $threshold || ! $product->vendor?->user) {
            return null;
        }

        return $this->send(
            $product->vendor->user,
            'Low stock alert',
            $product->name.' has only '.$product->stock_quantity.' item(s) left.',
            'low_stock_alert',
            ['database', 'mail']
        );
    }

    public function markRead(Notification $notification): Notification
    {
        $notification->markAsRead();

        return $notification->refresh();
    }

    public function markUnread(Notification $notification): Notification
    {
        $notification->markAsUnread();

        return $notification->refresh();
    }

    private function adminUsers(): Collection
    {
        return User::whereHas('role', fn ($query) => $query->whereIn('name', ['Admin', 'Super Admin']))->get();
    }

    private function sendSmsPlaceholder(User $user, string $message): void
    {
        // SMS provider integration will be added later.
    }

    private function sendWhatsAppPlaceholder(User $user, string $message): void
    {
        // WhatsApp provider integration will be added later.
    }

    private function sendPushPlaceholder(User $user, string $title, string $message): void
    {
        // Push provider integration will be added later.
    }
}
