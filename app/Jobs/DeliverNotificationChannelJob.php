<?php

namespace App\Jobs;

use App\Exceptions\InvalidDeviceTokenException;
use App\Mail\GenericNotificationMail;
use App\Mail\OrderInvoiceMail;
use App\Models\NotificationDeliveryLog;
use App\Models\NotificationPreference;
use App\Models\Order;
use App\Services\ExternalSmsService;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class DeliverNotificationChannelJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $deliveryLogId)
    {
        $this->afterCommit();
    }

    public function tries(): int
    {
        return max(1, min(10, (int) config('services.notifications.max_attempts', 3)));
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [15, 60, 300];
    }

    public function handle(
        ExternalSmsService $sms,
        FirebaseCloudMessagingService $firebase,
    ): void {
        $delivery = NotificationDeliveryLog::query()
            ->with(['notification', 'user'])
            ->findOrFail($this->deliveryLogId);

        if (in_array($delivery->status, [
            NotificationDeliveryLog::STATUS_SENT,
            NotificationDeliveryLog::STATUS_SKIPPED,
        ], true)) {
            return;
        }

        $delivery->increment('attempts');
        $delivery->refresh()->update([
            'status' => NotificationDeliveryLog::STATUS_SENDING,
            'failure_reason' => null,
        ]);

        try {
            match ($delivery->channel) {
                'email' => $this->deliverEmail($delivery),
                'sms' => $this->deliverSms($delivery, $sms),
                'firebase' => $this->deliverFirebase($delivery, $firebase),
                default => $this->skip($delivery, 'No delivery provider is configured for this channel.'),
            };
        } catch (Throwable $exception) {
            $delivery->refresh()->update([
                'status' => $this->isNonRetryable($delivery, $exception)
                    ? NotificationDeliveryLog::STATUS_FAILED
                    : NotificationDeliveryLog::STATUS_QUEUED,
                'failure_reason' => Str::limit($exception->getMessage(), 1000, ''),
            ]);

            if ($this->isNonRetryable($delivery, $exception)) {
                return;
            }

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        NotificationDeliveryLog::query()
            ->whereKey($this->deliveryLogId)
            ->whereNotIn('status', [
                NotificationDeliveryLog::STATUS_SENT,
                NotificationDeliveryLog::STATUS_SKIPPED,
            ])
            ->update([
                'status' => NotificationDeliveryLog::STATUS_FAILED,
                'failure_reason' => $exception
                    ? Str::limit($exception->getMessage(), 1000, '')
                    : 'The delivery job exhausted its retry limit.',
            ]);
    }

    private function deliverEmail(NotificationDeliveryLog $delivery): void
    {
        if (blank($delivery->user?->email)) {
            $this->skip($delivery, 'The recipient has no email address.');

            return;
        }

        $notification = $delivery->notification;

        if ($notification?->type === 'order_invoice') {
            $order = Order::query()
                ->with(['customer.user', 'vendor', 'items', 'payment', 'delivery.rider.user'])
                ->find($notification->order_id);

            if (! $order) {
                $this->skip($delivery, 'The order for this invoice no longer exists.');

                return;
            }

            Mail::to($delivery->user->email)->send(new OrderInvoiceMail($order));
        } else {
            Mail::to($delivery->user->email)->send(
                new GenericNotificationMail(
                    $notification->title,
                    $notification->message,
                    is_array($notification->data['email_details'] ?? null)
                        ? $notification->data['email_details']
                        : [],
                    $notification->data['email_action_url'] ?? null,
                    $notification->data['email_action_label'] ?? null,
                ),
            );
        }
        $this->markSent($delivery);
    }

    private function deliverSms(NotificationDeliveryLog $delivery, ExternalSmsService $sms): void
    {
        if (blank($delivery->user?->phone)) {
            $this->skip($delivery, 'The recipient has no phone number.');

            return;
        }

        $sms->send($delivery->user->phone, $delivery->notification->message);
        $this->markSent($delivery);
    }

    private function deliverFirebase(
        NotificationDeliveryLog $delivery,
        FirebaseCloudMessagingService $firebase,
    ): void {
        if (! $firebase->isConfigured()) {
            $this->skip($delivery, 'Firebase is not configured. Set FIREBASE_PROJECT_ID and FIREBASE_CREDENTIALS on the server.');

            return;
        }

        $notification = $delivery->notification;
        $appRole = $notification?->app_role;

        if (! $notification || blank($appRole)) {
            $this->skip($delivery, 'The notification is not associated with a mobile app role.');

            return;
        }

        $preference = NotificationPreference::firstOrCreate([
            'user_id' => $delivery->user_id,
            'app_role' => $appRole,
        ]);

        if (! $preference->allows($notification->type)) {
            $this->skip($delivery, 'The recipient disabled this notification category.');

            return;
        }

        $devices = $delivery->user->deviceTokens()
            ->active()
            ->where('app_role', $appRole)
            ->get();

        if ($devices->isEmpty()) {
            $this->skip($delivery, 'The recipient has no active device token for this app.');

            return;
        }

        $payload = [
            ...($notification->data ?? []),
            'notification_id' => $notification->id,
            'event_id' => $delivery->event_id,
            'type' => $notification->type,
            'app_role' => $appRole,
            'deep_link' => $notification->deep_link,
        ];

        $sent = 0;
        foreach ($devices as $device) {
            try {
                $firebase->sendToToken(
                    $device->token,
                    $notification->title,
                    $notification->message,
                    $payload,
                    'dailycart_'.$appRole,
                );
                $device->update(['last_used_at' => now()]);
                $sent++;
            } catch (InvalidDeviceTokenException) {
                $device->update(['revoked_at' => now()]);
            }
        }

        if ($sent === 0) {
            $this->skip($delivery, 'Firebase rejected all active device tokens.');

            return;
        }

        $this->markSent($delivery);
    }

    private function markSent(NotificationDeliveryLog $delivery): void
    {
        $delivery->refresh()->update([
            'status' => NotificationDeliveryLog::STATUS_SENT,
            'failure_reason' => null,
            'delivered_at' => now(),
        ]);
    }

    private function skip(NotificationDeliveryLog $delivery, string $reason): void
    {
        $delivery->refresh()->update([
            'status' => NotificationDeliveryLog::STATUS_SKIPPED,
            'failure_reason' => $reason,
        ]);
    }

    private function isNonRetryable(NotificationDeliveryLog $delivery, Throwable $exception): bool
    {
        return $delivery->channel === 'sms'
            && str_contains(strtolower($exception->getMessage()), 'insufficient sms credit');
    }
}
