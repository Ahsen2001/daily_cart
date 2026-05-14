<?php

namespace App\Services;

use App\Models\Notification as DailyCartNotification;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderConfirmedNotification;
use App\Notifications\OrderPackedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    public const STATUSES = [
        'pending',
        'confirmed',
        'packed',
        'assigned_to_rider',
        'out_for_delivery',
        'delivered',
        'cancelled',
        'refunded',
    ];

    public function confirm(Order $order): Order
    {
        $this->ensureStatus($order, 'pending', 'Vendor can confirm only pending orders.');

        return DB::transaction(function () use ($order) {
            $order->update(['order_status' => 'confirmed']);
            $this->notify($order->customer->user, new OrderConfirmedNotification($order));

            return $order->refresh();
        });
    }

    public function pack(Order $order): Order
    {
        $this->ensureStatus($order, 'confirmed', 'Vendor can mark packed only confirmed orders.');

        return DB::transaction(function () use ($order) {
            $order->update(['order_status' => 'packed']);
            $this->notify($order->customer->user, new OrderPackedNotification($order));

            return $order->refresh();
        });
    }

    public function cancel(Order $order, string $reason, string $message = 'This order cannot be cancelled.'): Order
    {
        if (! in_array($order->order_status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages(['order_status' => $message]);
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->update([
                'order_status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            $this->notify($order->customer->user, new OrderCancelledNotification($order));

            return $order->refresh();
        });
    }

    public function adminUpdate(Order $order, string $status): Order
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw ValidationException::withMessages(['order_status' => 'Invalid order status.']);
        }

        return DB::transaction(function () use ($order, $status) {
            $order->update(['order_status' => $status]);

            return $order->refresh();
        });
    }

    public function notify(?User $user, Notification $notification): void
    {
        if (! $user) {
            return;
        }

        $payload = $notification->toArray($user);

        DailyCartNotification::create([
            'user_id' => $user->id,
            'title' => $payload['title'],
            'message' => $payload['message'],
            'type' => $notification::class,
        ]);
    }

    private function ensureStatus(Order $order, string $expected, string $message): void
    {
        if ($order->order_status !== $expected) {
            throw ValidationException::withMessages(['order_status' => $message]);
        }
    }
}
