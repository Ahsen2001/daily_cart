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

        $smsRecipientIds = $this->smsRecipientsForStatus($order, $status, $actor)->pluck('id')->all();

        $this->recipients($order, $actor)->each(function (User $user) use ($title, $message, $order, $status, $smsRecipientIds) {
            $this->notifications->sendOnce(
                $user,
                $title,
                $message,
                'order_update:'.$order->id.':'.$status,
                array_merge(['database', 'mail'], in_array($user->id, $smsRecipientIds, true) ? ['sms'] : []),
            );
        });
    }

    public function riderAssigned(Order $order, ?User $actor = null): void
    {
        $order->loadMissing(['customer.user', 'vendor.user', 'delivery.rider.user']);
        $title = 'Rider assigned: '.$order->order_number;
        $message = 'A rider has been assigned to order '.$order->order_number.'.';

        $smsRecipientIds = collect([$order->customer?->user, $order->delivery?->rider?->user])
            ->filter()->pluck('id')->all();

        $this->recipients($order, $actor)->each(function (User $user) use ($title, $message, $order, $smsRecipientIds) {
            $this->notifications->sendOnce(
                $user,
                $title,
                $message,
                'rider_assigned:'.$order->id,
                array_merge(['database', 'mail'], in_array($user->id, $smsRecipientIds, true) ? ['sms'] : []),
            );
        });
    }

    /** @return Collection<int, User> */
    private function smsRecipientsForStatus(Order $order, string $status, ?User $actor): Collection
    {
        $customer = $order->customer?->user;
        $vendor = $order->vendor?->user;
        $rider = $order->delivery?->rider?->user;
        $admins = $this->admins($actor);

        if ($actor?->hasRole('Vendor')) {
            $recipients = match ($status) {
                'confirmed', 'cancelled' => collect([$customer])->merge($admins),
                'packed' => collect([$customer]),
                default => $this->recipients($order, $actor),
            };
        } elseif ($actor?->hasRole('Rider')) {
            $recipients = match ($status) {
                'delivery_accepted', 'picked_up' => $admins,
                'out_for_delivery' => collect([$customer]),
                'delivered', 'delivery_failed' => collect([$customer, $vendor])->merge($admins),
                default => $this->recipients($order, $actor),
            };
        } else {
            // Admin and Super Admin status changes notify every other stakeholder.
            $recipients = $this->recipients($order, $actor);
        }

        return $recipients->filter()
            ->filter(fn (User $user) => $actor?->id !== $user->id)
            ->unique('id')
            ->values();
    }

    /** @return Collection<int, User> */
    private function recipients(Order $order, ?User $actor): Collection
    {
        return collect([
            $order->customer?->user,
            $order->vendor?->user,
            $order->delivery?->rider?->user,
        ])->filter()
            ->merge($this->admins($actor))
            ->filter(fn (User $user) => $actor?->id !== $user->id)
            ->unique('id')
            ->values();
    }

    /** @return Collection<int, User> */
    private function admins(?User $actor): Collection
    {
        return User::query()->whereHas('roles', fn ($query) => $query->whereIn('name', ['Admin', 'Super Admin']))
            ->when($actor, fn ($query) => $query->whereKeyNot($actor->id))
            ->get();
    }
}
