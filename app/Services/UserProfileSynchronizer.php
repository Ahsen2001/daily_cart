<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Str;

class UserProfileSynchronizer
{
    /**
     * Ensure every role-backed user has the profile required by the rest of the application.
     * Existing profiles are never overwritten by this fallback synchronizer.
     */
    public function ensureFor(User $user): void
    {
        $role = $user->role?->name ?? $user->getRoleNames()->first();

        match ($role) {
            'Customer' => $this->ensureCustomer($user),
            'Vendor' => $this->ensureVendor($user),
            'Rider' => $this->ensureRider($user),
            'Admin', 'Super Admin' => $this->ensureAdmin($user, $role),
            default => null,
        };
    }

    private function ensureCustomer(User $user): void
    {
        $profile = Customer::withTrashed()->firstOrNew(['user_id' => $user->id]);

        if ($profile->exists) {
            $profile->restore();

            return;
        }

        $profile->fill([
            'first_name' => Str::before($user->name, ' ') ?: $user->name,
            'last_name' => Str::after($user->name, ' ') ?: null,
            'phone' => $user->phone ?? '',
            'status' => 'active',
        ])->save();
    }

    private function ensureVendor(User $user): void
    {
        $profile = Vendor::withTrashed()->firstOrNew(['user_id' => $user->id]);

        if ($profile->exists) {
            $profile->restore();

            return;
        }

        $profile->fill([
            'store_name' => $user->name."'s Store",
            'phone' => $user->phone ?? '',
            'address' => 'Profile details pending',
            'city' => 'Pending',
            'district' => 'Pending',
            'status' => 'pending',
            'commission_rate' => 0,
        ])->save();
    }

    private function ensureRider(User $user): void
    {
        $profile = Rider::withTrashed()->firstOrNew(['user_id' => $user->id]);

        if ($profile->exists) {
            $profile->restore();

            return;
        }

        $profile->fill([
            'vehicle_type' => 'bicycle',
            'availability_status' => 'unavailable',
            'verification_status' => 'pending',
        ])->save();
    }

    private function ensureAdmin(User $user, string $role): void
    {
        $profile = Admin::withTrashed()->firstOrNew(['user_id' => $user->id]);

        if ($profile->exists) {
            $profile->restore();

            return;
        }

        $profile->fill([
            'access_level' => $role === 'Super Admin' ? 'super_admin' : 'admin',
            'status' => $user->status === 'inactive' ? 'inactive' : 'active',
        ])->save();
    }
}
