<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Schema;

class DeliveryTrackingRelationLoader
{
    private ?bool $hasTrackingStates = null;

    private ?bool $hasCurrentLocations = null;

    public function hasTrackingStates(): bool
    {
        return $this->hasTrackingStates ??= Schema::hasTable('delivery_tracking_states');
    }

    public function hasCurrentLocations(): bool
    {
        return $this->hasCurrentLocations ??= Schema::hasTable('delivery_current_locations');
    }

    public function load(Order $order): Order
    {
        $order->loadMissing('delivery');
        $delivery = $order->delivery;
        if (! $delivery) {
            return $order;
        }

        if ($this->hasTrackingStates()) {
            $delivery->loadMissing('trackingState');
        } else {
            $delivery->setRelation('trackingState', null);
        }

        if ($this->hasCurrentLocations()) {
            $delivery->loadMissing('currentLocation');
        } else {
            $delivery->setRelation('currentLocation', null);
        }

        return $order;
    }
}
