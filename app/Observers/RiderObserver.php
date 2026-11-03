<?php

namespace App\Observers;

use App\Models\Rider;
use App\Services\DeliveryTrackingService;

class RiderObserver
{
    public function updated(Rider $rider): void
    {
        if ($rider->wasChanged('verification_status') && $rider->verification_status !== 'verified') {
            app(DeliveryTrackingService::class)->stopRider($rider, 'rider_authorization_revoked');
        }
    }
}
