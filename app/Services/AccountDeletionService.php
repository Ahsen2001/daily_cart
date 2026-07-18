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

            $this->deleteVendorResources($user->vendor);

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

        $vendor->products()->delete();
        $vendor->coupons()->delete();
        $vendor->promotions()->delete();
        $vendor->advertisements()->delete();
        $vendor->subscriptions()->delete();
    }
}
