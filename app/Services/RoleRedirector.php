<?php

namespace App\Services;

use App\Models\User;

class RoleRedirector
{
    public function dashboardRouteName(User $user): string
    {
        return match (true) {
            $user->hasRole('Super Admin') => 'super-admin.dashboard',
            $user->hasRole('Admin') => 'admin.dashboard',
            $user->hasRole('Vendor') => 'vendor.dashboard',
            $user->hasRole('Rider') => 'rider.dashboard',
            default => 'customer.dashboard',
        };
    }
}
