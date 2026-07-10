<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'vendor_id' => $this->vendor_id,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'delivery_fee' => (float) $this->delivery_fee,
            'service_charge' => (float) $this->service_charge,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'delivery_address' => $this->delivery_address,
            'delivery_latitude' => $this->delivery_latitude ? (float) $this->delivery_latitude : null,
            'delivery_longitude' => $this->delivery_longitude ? (float) $this->delivery_longitude : null,
            'order_status' => $this->order_status,
            'payment_status' => $this->payment_status,
            'placed_at' => $this->placed_at,
            'scheduled_delivery_at' => $this->scheduled_delivery_at,
            'timeline' => $this->statusHistories->map(fn ($history) => [
                'status' => $history->status,
                'remarks' => $history->remarks,
                'timestamp' => $history->created_at,
            ]),
        ];
    }
}
