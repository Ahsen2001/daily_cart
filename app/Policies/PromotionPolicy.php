<?php

namespace App\Policies;

use App\Models\Promotion;
use App\Models\User;

class PromotionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Promotion $promotion): bool
    {
        return $promotion->vendor_id === null || $user->vendor?->id === $promotion->vendor_id;
    }

    public function update(User $user, Promotion $promotion): bool
    {
        return $user->vendor?->id === $promotion->vendor_id;
    }

    public function delete(User $user, Promotion $promotion): bool
    {
        return $this->update($user, $promotion);
    }
}
