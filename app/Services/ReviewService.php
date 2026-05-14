<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function create(User $user, Order $order, Product $product, int $rating, ?string $comment = null, ?UploadedFile $image = null): Review
    {
        $customer = $user->customer;

        if (! $customer || $order->customer_id !== $customer->id) {
            throw ValidationException::withMessages(['order' => 'You can review only your own delivered orders.']);
        }

        if ($order->order_status !== 'delivered') {
            throw ValidationException::withMessages(['order' => 'Customers can review only delivered products.']);
        }

        $purchased = $order->items()->where('product_id', $product->id)->exists();

        if (! $purchased) {
            throw ValidationException::withMessages(['product' => 'You can review only products you purchased.']);
        }

        if (Review::where('customer_id', $customer->id)->where('order_id', $order->id)->where('product_id', $product->id)->exists()) {
            throw ValidationException::withMessages(['review' => 'You have already reviewed this product for this order.']);
        }

        return DB::transaction(function () use ($customer, $order, $product, $rating, $comment, $image) {
            $review = Review::create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'order_id' => $order->id,
                'rating' => $rating,
                'comment' => $comment,
                'image' => $image?->store('reviews', 'public'),
                'status' => 'visible',
            ]);

            if ($product->vendor?->user) {
                $this->notifications->send(
                    $product->vendor->user,
                    'Customer review received',
                    'A customer reviewed '.$product->name.'.',
                    'customer_review_received'
                );
            }

            return $review;
        });
    }

    public function hide(Review $review): Review
    {
        $review->update(['status' => 'hidden']);

        return $review->refresh();
    }

    public function report(Review $review): Review
    {
        $review->update(['status' => 'reported']);

        return $review->refresh();
    }

    public function delete(Review $review): void
    {
        if ($review->image) {
            Storage::disk('public')->delete($review->image);
        }

        $review->delete();
    }

    public function analytics(): array
    {
        return [
            'average_rating' => round((float) Review::where('status', 'visible')->avg('rating'), 2),
            'review_count' => Review::where('status', 'visible')->count(),
            'reported_count' => Review::where('status', 'reported')->count(),
            'hidden_count' => Review::where('status', 'hidden')->count(),
        ];
    }
}
