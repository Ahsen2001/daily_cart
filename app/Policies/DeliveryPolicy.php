<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Delivery $delivery): bool
    {
        return $user->rider?->id === $delivery->rider_id
            || $user->customer?->id === $delivery->order?->customer_id;
    }

    public function update(User $user, Delivery $delivery): bool
    {
        return $user->rider?->id === $delivery->rider_id;
    }
}
