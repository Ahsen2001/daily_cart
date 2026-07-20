<?php

namespace App\Jobs;

use App\Exceptions\InvalidDeviceTokenException;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly int $notificationId)
    {
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 60, 300, 900];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(6);
    }

    public function handle(FirebaseCloudMessagingService $firebase): void
    {
        $notification = Notification::query()->with('user')->find($this->notificationId);

        if (! $notification?->user || blank($notification->app_role)) {
            return;
        }

        $preference = NotificationPreference::firstOrCreate(
            ['user_id' => $notification->user_id, 'app_role' => $notification->app_role],
        );

        if (! $preference->allows($notification->type)) {
            return;
        }

        $payload = [
            ...($notification->data ?? []),
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'app_role' => $notification->app_role,
            'deep_link' => $notification->deep_link,
        ];

        $notification->user->deviceTokens()
            ->active()
            ->where('app_role', $notification->app_role)
            ->get()
            ->each(function ($device) use ($firebase, $notification, $payload): void {
                try {
                    $firebase->sendToToken(
                        $device->token,
                        $notification->title,
                        $notification->message,
                        $payload,
                        'dailycart_'.$notification->app_role,
                    );
                    $device->update(['last_used_at' => now()]);
                } catch (InvalidDeviceTokenException) {
                    $device->update(['revoked_at' => now()]);
                }
            });
    }

    public function failed(?Throwable $exception): void
    {
        report($exception);
    }
}
