<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->customer?->id === $order->customer_id
            || $user->vendor?->id === $order->vendor_id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->vendor?->id === $order->vendor_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->customer?->id === $order->customer_id && $order->order_status === 'pending';
    }

    public function manage(User $user, Order $order): bool
    {
        return $user->vendor?->id === $order->vendor_id;
    }
}
