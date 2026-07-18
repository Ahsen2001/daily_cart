<?php

namespace App\Services;

use App\Models\User;

class RoleRedirector
{
    public function dashboardRouteName(User $user): string
    {
        $roleName = $user->role?->name;

        return match ($roleName) {
            'Super Admin' => 'super-admin.dashboard',
            'Admin' => 'admin.dashboard',
            'Vendor' => 'vendor.dashboard',
            'Rider' => 'rider.dashboard',
            default => 'customer.dashboard',
        };
    }
}
