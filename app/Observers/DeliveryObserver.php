<?php

namespace App\Observers;

use App\Models\Delivery;
use App\Services\DeliveryTrackingService;

class DeliveryObserver
{
    public function updated(Delivery $delivery): void
    {
        $terminal = ! in_array($delivery->status, DeliveryTrackingService::ACTIVE_STATUSES, true);
        if ($delivery->wasChanged('rider_id') || ($delivery->wasChanged('status') && $terminal)) {
            app(DeliveryTrackingService::class)->stopDelivery(
                $delivery,
                $delivery->wasChanged('rider_id') ? 'delivery_reassigned' : 'delivery_'.$delivery->status,
            );
        }
    }

    public function deleted(Delivery $delivery): void
    {
        app(DeliveryTrackingService::class)->stopDelivery($delivery, 'delivery_removed');
    }
}
