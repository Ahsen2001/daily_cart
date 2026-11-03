<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\DeliveryTrackingService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->wasChanged('order_status')
            && in_array($order->order_status, ['cancelled', 'refunded', 'delivered'], true)
            && $order->delivery) {
            app(DeliveryTrackingService::class)->stopDelivery($order->delivery, 'order_'.$order->order_status);
        }
    }
}
