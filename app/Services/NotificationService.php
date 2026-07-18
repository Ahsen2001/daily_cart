<?php

namespace App\Services;

use App\Jobs\SendNotificationChannelJob;
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
            Mail::to($user->email)->queue(
                (new GenericNotificationMail($title, $message))->afterCommit()
            );
        }

        if (in_array('sms', $channels, true)) {
            SendNotificationChannelJob::dispatch($user->id, 'sms', $title, $message)->afterCommit();
        }

        if (in_array('whatsapp', $channels, true)) {
            SendNotificationChannelJob::dispatch($user->id, 'whatsapp', $title, $message)->afterCommit();
        }

        if (in_array('push', $channels, true)) {
            SendNotificationChannelJob::dispatch($user->id, 'push', $title, $message)->afterCommit();
        }

        return $notification;
    }

    public function notifyAdmins(string $title, string $message, string $type, array $channels = ['database']): void
    {
        $this->adminUsers()->each(fn (User $user) => $this->send($user, $title, $message, $type, $channels));
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
        return User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Admin', 'Super Admin']))->get();
    }
}
