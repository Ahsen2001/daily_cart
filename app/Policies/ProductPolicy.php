<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Product $product): bool
    {
        return $this->belongsToVendor($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->vendor?->status === 'approved';
    }

    public function update(User $user, Product $product): bool
    {
        return $this->belongsToVendor($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->belongsToVendor($user, $product);
    }

    private function belongsToVendor(User $user, Product $product): bool
    {
        return $user->vendor?->id === $product->vendor_id;
    }
}
