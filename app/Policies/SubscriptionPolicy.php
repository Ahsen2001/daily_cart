<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->isAdminUser()
            || $user->customer?->id === $subscription->customer_id
            || $user->vendor?->id === $subscription->vendor_id;
    }

    public function manage(User $user, Subscription $subscription): bool
    {
        return $user->isAdminUser() || $user->customer?->id === $subscription->customer_id;
    }
}
