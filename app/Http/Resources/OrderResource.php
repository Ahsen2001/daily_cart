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
            'delivery_distance_meters' => $this->delivery_distance_meters,
            'order_status' => $this->order_status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment?->payment_method,
            'placed_at' => $this->placed_at,
            'created_at' => $this->created_at,
            'scheduled_delivery_at' => $this->scheduled_delivery_at,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant?->name,
                'image' => $item->product?->image_url,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ])),
            'rider' => $this->whenLoaded('delivery', function () {
                $rider = $this->delivery?->rider;

                return $rider ? [
                    'id' => $rider->id,
                    'name' => $rider->user?->name,
                    'phone' => $rider->user?->phone,
                    'latitude' => $rider->current_latitude,
                    'longitude' => $rider->current_longitude,
                ] : null;
            }),
            'delivery' => $this->whenLoaded('delivery', fn () => $this->delivery ? [
                'id' => $this->delivery->id,
                'status' => $this->delivery->status,
                'scheduled_at' => $this->delivery->scheduled_at,
                'accepted_at' => $this->delivery->accepted_at,
                'picked_up_at' => $this->delivery->picked_up_at,
                'delivered_at' => $this->delivery->delivered_at,
            ] : null),
            'timeline' => $this->whenLoaded('statusHistories', fn () => $this->statusHistories->map(fn ($history) => [
                'status' => $history->status,
                'remarks' => $history->remarks,
                'timestamp' => $history->created_at,
            ])),
        ];
    }
}
