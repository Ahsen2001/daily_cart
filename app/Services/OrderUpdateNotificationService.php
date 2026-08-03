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
                array_merge(['database', 'mail', 'push'], in_array($user->id, $smsRecipientIds, true) ? ['sms'] : []),
                [
                    'order_id' => $order->id,
                    'delivery_id' => $order->delivery?->id,
                    'status' => $status,
                ],
            );
        });
    }

    public function riderAssigned(Order $order, ?User $actor = null): void
    {
        $order->loadMissing(['customer.user', 'vendor.user', 'delivery.rider.user']);
        $rider = $order->delivery?->rider?->user;
        $delivery = $order->delivery;
        if ($rider && $delivery) {
            $customer = $order->customer;
            $customerName = trim(implode(' ', array_filter([
                $customer?->first_name,
                $customer?->last_name,
            ]))) ?: ($customer?->user?->name ?: 'Customer');
            $customerContact = $customer?->user?->phone ?: ($customer?->phone ?: 'Not provided');
            $pickupAddress = $delivery->pickup_address ?: ($order->vendor?->address ?: 'Vendor location');
            $deliveryAddress = $delivery->delivery_address ?: ($order->delivery_address ?: 'Not provided');
            $scheduledAt = $delivery->scheduled_at ?: $order->scheduled_delivery_at;
            $scheduledLabel = $scheduledAt?->format('M d, Y h:i A') ?: 'As soon as possible';
            $dashboardUrl = route('rider.deliveries.show', $delivery);

            $this->notifications->sendOnce(
                $rider,
                'New delivery assigned',
                'Order '.$order->order_number.' has been assigned to you. Pickup: '.$pickupAddress.'.',
                'delivery_assigned',
                ['database', 'mail', 'sms', 'push'],
                [
                    'order_id' => $order->id,
                    'delivery_id' => $delivery->id,
                    'status' => 'assigned',
                    'order_number' => $order->order_number,
                    'customer_name' => $customerName,
                    'customer_contact' => $customerContact,
                    'pickup_address' => $pickupAddress,
                    'delivery_address' => $deliveryAddress,
                    'scheduled_delivery_at' => $scheduledAt?->toIso8601String(),
                    'email_details' => [
                        'Order number' => $order->order_number,
                        'Customer' => $customerName,
                        'Customer contact' => $customerContact,
                        'Pickup address' => $pickupAddress,
                        'Delivery address' => $deliveryAddress,
                        'Scheduled delivery time' => $scheduledLabel,
                    ],
                    'email_action_url' => $dashboardUrl,
                    'email_action_label' => 'Open delivery in DailyCart',
                ],
                '/delivery-details/'.$delivery->id,
                'rider',
                'delivery-assigned-'.$delivery->id,
            );
        }

        $title = 'Rider assigned: '.$order->order_number;
        $message = 'A rider has been assigned to order '.$order->order_number.'.';
        collect([$order->customer?->user, $order->vendor?->user])
            ->filter()
            ->merge($this->admins($actor))
            ->filter(fn (User $user) => $actor?->id !== $user->id)
            ->unique('id')
            ->each(function (User $user) use ($title, $message, $order) {
            $this->notifications->sendOnce(
                $user,
                $title,
                $message,
                'rider_assigned:'.$order->id,
                array_merge(['database', 'mail', 'push'], $user->id === $order->customer?->user?->id ? ['sms'] : []),
                [
                    'order_id' => $order->id,
                    'delivery_id' => $order->delivery?->id,
                    'status' => 'rider_assigned',
                ],
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
