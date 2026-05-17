<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAdmin(User $user): bool
    {
        return $user->isAdminUser();
    }

    public function viewVendor(User $user): bool
    {
        return $user->hasPrimaryRole('Vendor') && $user->vendor?->status === 'approved';
    }

    public function viewRider(User $user): bool
    {
        return $user->hasPrimaryRole('Rider') && in_array($user->rider?->verification_status, ['verified', 'approved'], true);
    }
}
