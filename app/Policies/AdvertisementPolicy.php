<?php

namespace App\Policies;

use App\Models\User;

class AdvertisementPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdminUser();
    }
}
