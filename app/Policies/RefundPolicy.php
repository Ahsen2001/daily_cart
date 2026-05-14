<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Refund $refund): bool
    {
        return $user->customer?->id === $refund->order?->customer_id
            || $user->vendor?->id === $refund->order?->vendor_id;
    }

    public function createForOrder(User $user, Order $order): bool
    {
        return $user->customer?->id === $order->customer_id
            && $order->order_status === 'delivered'
            && $order->payment?->status === 'paid';
    }

    public function process(User $user, Refund $refund): bool
    {
        return $user->isAdminUser();
    }
}
