<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class WalletPolicy
{
    public function view(User $user, Customer $customer): bool
    {
        return $user->customer?->id === $customer->id || $user->isAdminUser();
    }

    public function topUp(User $user, Customer $customer): bool
    {
        return $user->customer?->id === $customer->id;
    }
}
