<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\RiderRating;
use App\Models\User;

class RiderRatingPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, RiderRating $rating): bool
    {
        return $user->customer?->id === $rating->customer_id
            || $user->rider?->id === $rating->rider_id;
    }

    public function rateOrder(User $user, Order $order): bool
    {
        return $user->customer?->id === $order->customer_id
            && $order->order_status === 'delivered'
            && $order->delivery?->status === 'delivered'
            && $order->delivery?->rider_id !== null;
    }

    public function report(User $user, RiderRating $rating): bool
    {
        return $user->rider?->id === $rating->rider_id;
    }

    public function moderate(User $user, RiderRating $rating): bool
    {
        return $user->isAdminUser();
    }
}
