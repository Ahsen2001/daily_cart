<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\LoyaltyPointService;
use App\Services\OrderService;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCommerceController extends Controller
{
    public function coupons(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        $coupons = Coupon::query()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->get()
            ->reject(fn (Coupon $coupon) => $coupon->per_customer_limit
                && $coupon->redemptions()->where('customer_id', $customer->id)->count() >= $coupon->per_customer_limit)
            ->map(fn (Coupon $coupon) => $this->couponPayload($coupon));

        return response()->json(['coupons' => $coupons->values()]);
    }

    public function validateCoupon(
        Request $request,
        CartService $carts,
        OrderService $orders,
        CouponService $coupons
    ): JsonResponse {
        $validated = $request->validate(['code' => ['required', 'string', 'max:255']]);
        $customer = $request->user()->customer;
        $cart = $carts->activeCart($customer);
        $quote = $orders->quote($cart, $validated['code'], $customer);
        $coupon = $quote['coupon'];
        $coupons->validateOrFail($coupon);

        return response()->json([
            'coupon' => [
                ...$this->couponPayload($coupon),
                'discount' => (float) $quote['discount'],
                'discount_amount' => (float) $quote['discount'],
                'is_valid' => true,
            ],
            'quote' => $quote,
        ]);
    }

    public function removeCoupon(): JsonResponse
    {
        return response()->json(['message' => 'Coupon removed from checkout.']);
    }

    public function loyaltyBalance(Request $request, LoyaltyPointService $loyalty): JsonResponse
    {
        return response()->json([
            'balance' => $loyalty->balance($request->user()->customer),
        ]);
    }

    public function loyaltyHistory(Request $request): JsonResponse
    {
        return response()->json([
            'history' => $request->user()->customer->loyaltyPoints()->latest()->get(),
        ]);
    }

    public function cancelOrder(
        Request $request,
        Order $order,
        OrderStatusService $orders
    ): JsonResponse {
        $this->ensureOrderOwned($request, $order);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $order = $orders->cancel(
            $order,
            $validated['reason'],
            'Customer can cancel only pending or confirmed orders.',
            $request->user(),
        );

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order' => new OrderResource($order->load($this->orderRelations())),
        ]);
    }

    public function orderStatus(Request $request, Order $order): JsonResponse
    {
        $this->ensureOrderOwned($request, $order);

        return response()->json([
            'order' => new OrderResource($order->load($this->orderRelations())),
        ]);
    }

    public function productReviews(Product $product): JsonResponse
    {
        $reviews = $product->reviews()->where('status', 'visible')
            ->with('customer.user')
            ->latest()
            ->get()
            ->map(fn (Review $review) => $this->reviewPayload($review));

        return response()->json(['reviews' => $reviews]);
    }

    public function myReviews(Request $request): JsonResponse
    {
        $reviews = $request->user()->customer->reviews()
            ->with(['product', 'customer.user'])
            ->latest()
            ->get()
            ->map(fn (Review $review) => $this->reviewPayload($review, true));

        return response()->json(['reviews' => $reviews]);
    }

    public function storeReview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $customer = $request->user()->customer;
        $order = $customer->orders()->whereKey($validated['order_id'])->firstOrFail();
        abort_unless($order->order_status === 'delivered', 422, 'Only delivered products can be reviewed.');
        abort_unless($order->items()->where('product_id', $validated['product_id'])->exists(), 422);

        $review = Review::updateOrCreate([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'product_id' => $validated['product_id'],
        ], [
            'vendor_id' => $order->vendor_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'image' => $request->file('image')?->store('reviews', 'public'),
            'status' => 'visible',
        ]);

        return response()->json([
            'message' => 'Review saved successfully.',
            'review' => $this->reviewPayload($review->load(['product', 'customer.user']), true),
        ], $review->wasRecentlyCreated ? 201 : 200);
    }

    public function updateReview(Request $request, Review $review): JsonResponse
    {
        abort_unless($review->customer_id === $request->user()->customer?->id, 403);
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('reviews', 'public');
        }
        $review->update($validated);

        return response()->json([
            'message' => 'Review updated successfully.',
            'review' => $this->reviewPayload($review->refresh()->load(['product', 'customer.user']), true),
        ]);
    }

    public function destroyReview(Request $request, Review $review): JsonResponse
    {
        abort_unless($review->customer_id === $request->user()->customer?->id, 403);
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }

    private function couponPayload(Coupon $coupon): array
    {
        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'title' => $coupon->title,
            'description' => $coupon->description,
            'type' => $coupon->discount_type ?? $coupon->type,
            'discount' => (float) ($coupon->discount_value ?: $coupon->value),
            'min_order_amount' => (float) $coupon->minimum_order_amount,
            'expires_at' => $coupon->expires_at,
            'is_valid' => true,
        ];
    }

    private function reviewPayload(Review $review, bool $owned = false): array
    {
        return [
            'id' => $review->id,
            'order_id' => $review->order_id,
            'product_id' => $review->product_id,
            'product_name' => $review->product?->name,
            'product_image' => $review->product?->image ? url('storage/'.$review->product->image) : null,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'image' => $review->image ? url('storage/'.$review->image) : null,
            'customer_name' => $review->customer?->user?->name ?? 'Customer',
            'created_at' => $review->created_at,
            'can_edit' => $owned,
            'can_delete' => $owned,
        ];
    }

    private function ensureOrderOwned(Request $request, Order $order): void
    {
        abort_unless($order->customer_id === $request->user()->customer?->id, 403);
    }

    private function orderRelations(): array
    {
        return [
            'statusHistories',
            'items.product',
            'items.variant',
            'payment',
            'delivery.rider.user',
        ];
    }
}
