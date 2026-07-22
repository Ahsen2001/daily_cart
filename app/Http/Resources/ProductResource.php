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
            'discount_price' => $this->discount_price === null ? null : (float) $this->discount_price,
            'unit_type' => $this->unit_type,
            'weight' => $this->weight,
            'sku' => $this->sku,
            'stock_quantity' => (int) $this->stock_quantity,
            'image' => $this->display_image_url,
            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
            'is_subscription_eligible' => (bool) $this->is_subscription_eligible,
            'average_rating' => round((float) ($this->visible_reviews_avg_rating ?? $this->averageRating()), 1),
            'vendor_name' => $this->vendor?->store_name,
            'category_name' => $this->category?->name,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => url('storage/'.$image->image_path),
            ])),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'value' => $variant->value,
                'price' => $variant->price === null ? null : (float) $variant->price,
                'stock_quantity' => (int) ($variant->inventory?->quantity ?? $this->stock_quantity),
            ])),
            'reviews' => $this->whenLoaded('reviews', fn () => $this->reviews->map(fn ($review) => [
                'id' => $review->id,
                'user_name' => $review->customer?->user?->name ?? 'Customer',
                'rating' => (float) $review->rating,
                'comment' => $review->comment,
            ])),
        ];
    }
}
