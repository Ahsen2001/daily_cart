<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class OrderUpdateNotificationService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function statusChanged(Order $order, string $status, ?User $actor = null): void
    {
        $order->loadMissing(['customer.user', 'vendor.user', 'delivery.rider.user']);
        $label = str($status)->replace('_', ' ')->title()->toString();
        $title = 'Order update: '.$order->order_number;
        $message = 'Order '.$order->order_number.' is now '.$label.'.';

        $this->recipients($order, $actor)->each(function (User $user) use ($title, $message, $order, $status) {
            $this->notifications->sendOnce(
                $user,
                $title,
                $message,
                'order_update:'.$order->id.':'.$status,
                ['database', 'mail', 'sms'],
            );
        });
    }

    public function riderAssigned(Order $order, ?User $actor = null): void
    {
        $order->loadMissing(['customer.user', 'vendor.user', 'delivery.rider.user']);
        $title = 'Rider assigned: '.$order->order_number;
        $message = 'A rider has been assigned to order '.$order->order_number.'.';

        $this->recipients($order, $actor)->each(function (User $user) use ($title, $message, $order) {
            $this->notifications->sendOnce(
                $user,
                $title,
                $message,
                'rider_assigned:'.$order->id,
                ['database', 'mail', 'sms'],
            );
        });
    }

    /** @return Collection<int, User> */
    private function recipients(Order $order, ?User $actor): Collection
    {
        return collect([
            $order->customer?->user,
            $order->vendor?->user,
            $order->delivery?->rider?->user,
        ])->filter()
            ->merge(User::query()->whereHas('roles', fn ($query) => $query->whereIn('name', ['Admin', 'Super Admin']))->get())
            ->filter(fn (User $user) => $actor?->id !== $user->id)
            ->unique('id')
            ->values();
    }
}
