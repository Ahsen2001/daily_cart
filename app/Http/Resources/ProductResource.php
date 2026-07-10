<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brandRelation?->name ?? $this->brand,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discount_price' => (float) $this->discount_price,
            'unit_type' => $this->unit_type,
            'weight' => $this->weight,
            'sku' => $this->sku,
            'stock_quantity' => (int) $this->stock_quantity,
            'image' => $this->image ? url('storage/' . $this->image) : null,
            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
            'is_subscription_eligible' => (bool) $this->is_subscription_eligible,
        ];
    }
}
