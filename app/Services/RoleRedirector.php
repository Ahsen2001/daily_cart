<?php

namespace App\Services;

use App\Models\User;

class RoleRedirector
{
    public function dashboardRouteName(User $user): string
    {
        return match ($user->role?->name) {
            'Super Admin' => 'super-admin.dashboard',
            'Admin' => 'admin.dashboard',
            'Vendor' => 'vendor.dashboard',
            'Rider' => 'rider.dashboard',
            default => 'customer.dashboard',
        };
    }
}
