<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Review $review): bool
    {
        return $user->customer?->id === $review->customer_id
            || $user->vendor?->id === $review->vendor_id;
    }

    public function createForOrderProduct(User $user, Order $order, Product $product): bool
    {
        return $user->customer?->id === $order->customer_id
            && $order->order_status === 'delivered'
            && $order->items()->where('product_id', $product->id)->exists()
            && ! Review::where('customer_id', $user->customer?->id)
                ->where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->exists();
    }

    public function moderate(User $user, Review $review): bool
    {
        return $user->isAdminUser();
    }
}
