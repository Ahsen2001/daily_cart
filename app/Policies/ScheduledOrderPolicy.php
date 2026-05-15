<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class ScheduledOrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->isAdminUser()
            || $user->customer?->id === $order->customer_id
            || $user->vendor?->id === $order->vendor_id
            || $user->rider?->id === $order->delivery?->rider_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->customer?->id === $order->customer_id && $order->order_status === 'pending';
    }
}
