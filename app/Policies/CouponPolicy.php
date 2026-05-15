<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $coupon->vendor_id === null || $user->vendor?->id === $coupon->vendor_id;
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->vendor?->id === $coupon->vendor_id;
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $this->update($user, $coupon);
    }
}
