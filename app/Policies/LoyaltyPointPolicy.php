<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class LoyaltyPointPolicy
{
    public function view(User $user, Customer $customer): bool
    {
        return $user->customer?->id === $customer->id || $user->isAdminUser();
    }

    public function manage(User $user): bool
    {
        return $user->isAdminUser();
    }
}
