<?php

namespace App\Services;

use App\Jobs\SendPublicPromotionPushJob;
use App\Jobs\SendPushNotificationJob;
use App\Jobs\SendNotificationChannelJob;
use App\Mail\GenericNotificationMail;
use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send(
        User $user,
        string $title,
        string $message,
        string $type,
        array $channels = ['database', 'push'],
        array $data = [],
        ?string $deepLink = null,
        ?string $appRole = null,
    ): Notification {
        $resolvedRole = $appRole ?? $this->appRoleFor($user);
        $resolvedDeepLink = $deepLink ?? $this->deepLinkFor($resolvedRole, $data);
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'app_role' => $resolvedRole,
            'data' => $data,
            'deep_link' => $resolvedDeepLink,
        ]);

        if (in_array('mail', $channels, true) && filled($user->email)) {
            Mail::to($user->email)->queue(
                (new GenericNotificationMail($title, $message))->afterCommit()
            );
        }

        if (in_array('sms', $channels, true) && filled($user->phone)) {
            SendNotificationChannelJob::dispatch($user->id, 'sms', $title, $message)->afterCommit();
        }

        if (in_array('whatsapp', $channels, true)) {
            SendNotificationChannelJob::dispatch($user->id, 'whatsapp', $title, $message)->afterCommit();
        }

        if (in_array('push', $channels, true)) {
            SendPushNotificationJob::dispatch($notification->id)->afterCommit();
        }

        return $notification;
    }

    public function sendOnce(
        User $user,
        string $title,
        string $message,
        string $type,
        array $channels = ['database', 'push'],
        array $data = [],
        ?string $deepLink = null,
        ?string $appRole = null,
    ): ?Notification {
        if (Notification::query()->where('user_id', $user->id)->where('type', $type)->exists()) {
            return null;
        }

        return $this->send($user, $title, $message, $type, $channels, $data, $deepLink, $appRole);
    }

    public function notifyAdmins(
        string $title,
        string $message,
        string $type,
        array $channels = ['database', 'push'],
        array $data = [],
        ?string $deepLink = null,
    ): void
    {
        $this->adminUsers()->each(
            fn (User $user) => $this->send($user, $title, $message, $type, $channels, $data, $deepLink)
        );
    }

    /**
     * Public campaigns are the only notification flow allowed to use an FCM topic.
     */
    public function sendPublicPromotion(
        string $title,
        string $message,
        array $data = [],
        string $appRole = 'customer',
    ): void {
        abort_unless(in_array($appRole, ['customer', 'vendor', 'rider'], true), 422, 'Invalid app role.');
        SendPublicPromotionPushJob::dispatch($appRole, $title, $message, $data)->afterCommit();
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
            ['database', 'mail', 'push'],
            ['product_id' => $product->id],
            '/vendor-product-details/'.$product->id,
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

    private function appRoleFor(User $user): ?string
    {
        $role = strtolower((string) ($user->role?->name ?? $user->roles()->value('name')));

        return in_array($role, ['customer', 'vendor', 'rider'], true) ? $role : null;
    }

    private function deepLinkFor(?string $appRole, array $data): ?string
    {
        if (isset($data['delivery_id']) && $appRole === 'rider') {
            return '/delivery-details/'.(int) $data['delivery_id'];
        }

        if (isset($data['order_id'])) {
            return match ($appRole) {
                'vendor' => '/vendor-order-details/'.(int) $data['order_id'],
                'rider' => isset($data['delivery_id'])
                    ? '/delivery-details/'.(int) $data['delivery_id']
                    : '/assigned-deliveries',
                default => '/order-details/'.(int) $data['order_id'],
            };
        }

        if (isset($data['support_ticket_id'])) {
            return match ($appRole) {
                'vendor' => '/vendor-support-ticket-details/'.(int) $data['support_ticket_id'],
                'rider' => '/rider-support-ticket-details/'.(int) $data['support_ticket_id'],
                default => '/support-ticket-details/'.(int) $data['support_ticket_id'],
            };
        }

        if (isset($data['promotion_id'])) {
            return '/promotion-details/'.(int) $data['promotion_id'];
        }

        return null;
    }
}
