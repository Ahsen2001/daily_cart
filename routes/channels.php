<?php

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('delivery.{deliveryId}', function (User $user, int $deliveryId): bool {
    $delivery = Delivery::query()->with('order:id,customer_id,vendor_id')->find($deliveryId);
    if (! $delivery) {
        return false;
    }

    return $user->isAdminUser()
        || $user->rider?->id === $delivery->rider_id
        || $user->customer?->id === $delivery->order?->customer_id
        || $user->vendor?->id === $delivery->order?->vendor_id;
});
