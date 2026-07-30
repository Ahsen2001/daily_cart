<?php

namespace App\Services;

use App\Jobs\DeliverNotificationChannelJob;
use App\Jobs\SendPublicPromotionPushJob;
use App\Models\Notification;
use App\Models\NotificationDeliveryLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

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
        ?string $eventId = null,
    ): Notification {
        $resolvedRole = $appRole ?? $this->appRoleFor($user);
        $resolvedDeepLink = $deepLink ?? $this->deepLinkFor($resolvedRole, $data);
        $resolvedEventId = $eventId ?? $this->eventIdFor(
            $user,
            $title,
            $message,
            $type,
            $data,
            $resolvedRole,
        );
        $orderId = isset($data['order_id']) ? (int) $data['order_id'] : null;

        $notification = Notification::firstOrCreate([
            'user_id' => $user->id,
            'event_id' => $resolvedEventId,
        ], [
            'order_id' => $orderId,
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'app_role' => $resolvedRole,
            'data' => $data,
            'deep_link' => $resolvedDeepLink,
        ]);

        if (in_array('database', $channels, true)) {
            $this->recordDatabaseDelivery($notification, $resolvedEventId, $orderId);
        }

        if (in_array('mail', $channels, true)) {
            $this->queueChannel($notification, $resolvedEventId, $orderId, 'email');
        }

        if (in_array('sms', $channels, true)) {
            $this->queueChannel($notification, $resolvedEventId, $orderId, 'sms');
        }

        if (in_array('push', $channels, true)) {
            $this->queueChannel($notification, $resolvedEventId, $orderId, 'firebase');
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
        ?string $eventId = null,
    ): ?Notification {
        $resolvedRole = $appRole ?? $this->appRoleFor($user);
        $resolvedEventId = $eventId ?? $this->eventIdFor(
            $user,
            $title,
            $message,
            $type,
            $data,
            $resolvedRole,
        );

        if (Notification::query()
            ->where('user_id', $user->id)
            ->where('event_id', $resolvedEventId)
            ->exists()) {
            return null;
        }

        return $this->send(
            $user,
            $title,
            $message,
            $type,
            $channels,
            $data,
            $deepLink,
            $resolvedRole,
            $resolvedEventId,
        );
    }

    public function notifyAdmins(
        string $title,
        string $message,
        string $type,
        array $channels = ['database', 'push'],
        array $data = [],
        ?string $deepLink = null,
    ): void {
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

    private function recordDatabaseDelivery(Notification $notification, string $eventId, ?int $orderId): void
    {
        NotificationDeliveryLog::firstOrCreate([
            'event_id' => $eventId,
            'user_id' => $notification->user_id,
            'channel' => 'database',
        ], [
            'notification_id' => $notification->id,
            'order_id' => $orderId,
            'status' => NotificationDeliveryLog::STATUS_SENT,
            'max_attempts' => $this->maxAttempts(),
            'delivered_at' => now(),
        ]);
    }

    private function queueChannel(
        Notification $notification,
        string $eventId,
        ?int $orderId,
        string $channel,
    ): void {
        $delivery = NotificationDeliveryLog::firstOrCreate([
            'event_id' => $eventId,
            'user_id' => $notification->user_id,
            'channel' => $channel,
        ], [
            'notification_id' => $notification->id,
            'order_id' => $orderId,
            'status' => NotificationDeliveryLog::STATUS_QUEUED,
            'max_attempts' => $this->maxAttempts(),
        ]);

        if ($delivery->wasRecentlyCreated) {
            DeliverNotificationChannelJob::dispatch($delivery->id)->afterCommit();
        }
    }

    private function eventIdFor(
        User $user,
        string $title,
        string $message,
        string $type,
        array $data,
        ?string $appRole,
    ): string {
        if (filled($data['event_id'] ?? null)) {
            return substr((string) $data['event_id'], 0, 64);
        }

        return hash('sha256', json_encode([
            'user_id' => $user->id,
            'app_role' => $appRole,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $this->normalizedData($data),
        ], JSON_THROW_ON_ERROR));
    }

    private function normalizedData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalizedData($value);
            }
        }

        ksort($data);

        return $data;
    }

    private function maxAttempts(): int
    {
        return max(1, min(10, (int) config('services.notifications.max_attempts', 3)));
    }
}
