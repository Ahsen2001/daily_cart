<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class AccountDeletionService
{
    /**
     * Remove a user from every active DailyCart area while retaining historical order records.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->loadMissing(['customer', 'vendor', 'rider', 'admin']);

            $user->tokens()->delete();
            $this->releaseUserIdentifiers($user);

            $this->deleteVendorResources($user->vendor);
            $user->rider?->forceFill(['license_number' => null])->saveQuietly();

            foreach ([$user->customer, $user->vendor, $user->rider, $user->admin] as $profile) {
                $profile?->delete();
            }

            $user->delete();
        });
    }

    /** Remove a vendor record left behind by an older account deletion flow. */
    public function deleteVendorProfile(Vendor $vendor): void
    {
        DB::transaction(function () use ($vendor) {
            $this->deleteVendorResources($vendor);
            $vendor->delete();
        });
    }

    private function deleteVendorResources(?Vendor $vendor): void
    {
        if (! $vendor) {
            return;
        }

        $vendor->forceFill(['business_registration_no' => null])->saveQuietly();
        $vendor->products()->delete();
        $vendor->coupons()->delete();
        $vendor->promotions()->delete();
        $vendor->advertisements()->delete();
        $vendor->subscriptions()->delete();
    }

    private function releaseUserIdentifiers(User $user): void
    {
        $user->forceFill([
            'email' => 'deleted+'.$user->id.'@deleted.dailycart.invalid',
            'phone' => null,
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'remember_token' => null,
        ])->saveQuietly();
    }
}
