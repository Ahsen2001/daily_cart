<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\VendorPayoutRequest;
use App\Services\AnalyticsService;
use App\Services\DashboardService;
use App\Services\FinanceReportService;
use App\Services\OrderStatusService;
use App\Services\ReportService;
use App\Services\ScheduledOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VendorBusinessController extends Controller
{
    public function dashboard(
        Request $request,
        DashboardService $dashboards,
        AnalyticsService $analytics
    ): JsonResponse {
        $vendor = $request->user()->vendor;
        $summary = $dashboards->vendorOverview($vendor);

        return response()->json([
            'dashboard' => [
                ...$summary,
                'pending_orders' => $vendor->orders()->where('order_status', 'pending')->count(),
                'today_sales' => (float) $vendor->orders()
                    ->whereDate('placed_at', today())
                    ->where('payment_status', 'paid')
                    ->sum('total_amount'),
                'total_earnings' => (float) ($summary['earnings'] ?? 0),
                'approval_status' => $vendor->status,
            ],
            'charts' => $analytics->vendorCharts($vendor->id, $request->only(['from', 'to', 'order_status'])),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['vendor' => $this->profilePayload($request)]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $vendor = $user->vendor;
        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'business_registration_number' => ['nullable', 'string', 'max:255'],
        ]);
        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }
        if ($validated['phone'] !== $user->phone) {
            $user->phone_verified_at = null;
        }
        $user->fill([
            'name' => $validated['owner_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ])->save();
        $vendor->update([
            'store_name' => $validated['shop_name'],
            'business_registration_no' => $validated['business_registration_number'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'formatted_address' => $validated['address'],
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'province' => $validated['province'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return response()->json([
            'message' => 'Vendor profile updated.',
            'vendor' => $this->profilePayload($request),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
        ]);
        $orders = $request->user()->vendor->orders()
            ->with($this->orderRelations())
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->latest()
            ->paginate(15);

        return response()->json([
            'orders' => collect($orders->items())->map(fn (Order $order) => $this->orderPayload($order)),
            'pagination' => $this->pagination($orders),
        ]);
    }

    public function order(Request $request, Order $order): JsonResponse
    {
        $this->ensureOrderOwned($request, $order);

        return response()->json([
            'order' => $this->orderPayload($order->load($this->orderRelations())),
        ]);
    }

    public function confirmOrder(
        Request $request,
        Order $order,
        OrderStatusService $orders
    ): JsonResponse {
        $this->ensureOrderOwned($request, $order);

        return $this->orderActionResponse(
            $orders->confirm($order, $request->user()),
            'Order confirmed.',
        );
    }

    public function packOrder(
        Request $request,
        Order $order,
        OrderStatusService $orders
    ): JsonResponse {
        $this->ensureOrderOwned($request, $order);

        return $this->orderActionResponse(
            $orders->pack($order, $request->user()),
            'Order marked as packed.',
        );
    }

    public function cancelOrder(
        Request $request,
        Order $order,
        OrderStatusService $orders
    ): JsonResponse {
        $this->ensureOrderOwned($request, $order);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        return $this->orderActionResponse(
            $orders->cancel(
                $order,
                $validated['reason'],
                'Vendor can cancel only pending or confirmed orders.',
                $request->user(),
            ),
            'Order cancelled.',
        );
    }

    public function earnings(Request $request, FinanceReportService $finance): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $vendor = $request->user()->vendor;
        $summary = $finance->vendorSummary($vendor, $validated['from'] ?? null, $validated['to'] ?? null);
        $wallet = $vendor->wallet;
        $orders = $vendor->orders()
            ->where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->latest('placed_at')
            ->limit(30)
            ->get();

        return response()->json([
            'earnings' => [
                'total_earnings' => (float) $summary['completed'],
                'today_earnings' => $finance->vendorEarningsSum($vendor, today()->toDateString(), today()->toDateString()),
                'weekly_earnings' => $finance->vendorEarningsSum($vendor, now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()),
                'monthly_earnings' => $finance->vendorEarningsSum($vendor, now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()),
                'platform_commission' => max(0, (float) $vendor->orders()->where('order_status', 'delivered')->sum('subtotal') - (float) $summary['completed']),
                'pending_payout' => (float) ($wallet?->pending_balance ?? $summary['pending']),
                'completed_payout' => (float) ($wallet?->total_withdrawn ?? 0),
                'refunded_amount' => (float) $summary['refunded'],
                'transactions' => $orders->map(fn (Order $order) => [
                    'title' => 'Order '.$order->order_number,
                    'amount' => $finance->vendorEarning($order),
                    'status' => 'earned',
                    'created_at' => $order->placed_at,
                ]),
            ],
        ]);
    }

    public function wallet(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;
        $wallet = $vendor->wallet;
        $payouts = $vendor->payoutRequests()->latest()->paginate(15);
        $reserved = (float) $vendor->payoutRequests()->whereIn('status', ['requested', 'approved'])->sum('amount');

        return response()->json([
            'wallet' => [
                'balance' => (float) ($wallet?->balance ?? 0),
                'available_balance' => max(0, (float) ($wallet?->balance ?? 0) - $reserved),
                'pending_balance' => (float) ($wallet?->pending_balance ?? 0),
                'total_earned' => (float) ($wallet?->total_earned ?? 0),
                'total_withdrawn' => (float) ($wallet?->total_withdrawn ?? 0),
                'currency' => 'LKR',
            ],
            'payouts' => collect($payouts->items())->map(fn (VendorPayoutRequest $payout) => $this->payoutPayload($payout)),
            'pagination' => $this->pagination($payouts),
        ]);
    }

    public function requestPayout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
            'branch' => ['nullable', 'string', 'max:255'],
        ]);
        $vendor = $request->user()->vendor;
        $balance = (float) ($vendor->wallet?->balance ?? 0);
        $reserved = (float) $vendor->payoutRequests()->whereIn('status', ['requested', 'approved'])->sum('amount');
        abort_if((float) $validated['amount'] > $balance - $reserved, 422, 'Insufficient available wallet balance.');
        $payout = $vendor->payoutRequests()->create([
            ...$validated,
            'status' => 'requested',
        ]);

        return response()->json([
            'message' => 'Payout request submitted.',
            'payout' => $this->payoutPayload($payout),
        ], 201);
    }

    public function reviews(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
        $reviews = Review::with(['customer.user', 'product'])
            ->where('vendor_id', $request->user()->vendor->id)
            ->when($validated['rating'] ?? null, fn ($query, $rating) => $query->where('rating', $rating))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15);

        return response()->json([
            'reviews' => collect($reviews->items())->map(fn (Review $review) => [
                'id' => $review->id,
                'product_id' => $review->product_id,
                'product_name' => $review->product?->name,
                'customer_name' => $review->customer?->user?->name ?? 'Customer',
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'image' => $review->image ? url('storage/'.$review->image) : null,
                'status' => $review->status,
                'created_at' => $review->created_at,
            ]),
            'pagination' => $this->pagination($reviews),
        ]);
    }

    public function refunds(Request $request): JsonResponse
    {
        $refunds = Refund::with(['order.customer.user', 'payment'])
            ->whereHas('order', fn ($query) => $query->where('vendor_id', $request->user()->vendor->id))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15);

        return response()->json([
            'refunds' => collect($refunds->items())->map(fn (Refund $refund) => $this->refundPayload($refund)),
            'pagination' => $this->pagination($refunds),
        ]);
    }

    public function respondToRefund(
        Request $request,
        Refund $refund
    ): JsonResponse {
        abort_unless($refund->order?->vendor_id === $request->user()->vendor?->id, 403);
        $validated = $request->validate(['vendor_note' => ['required', 'string', 'max:2000']]);
        $refund->update([
            'vendor_note' => $validated['vendor_note'],
            'vendor_responded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Refund response saved for administrator review.',
            'refund' => $this->refundPayload($refund->refresh()->load(['order.customer.user', 'payment'])),
        ]);
    }

    public function coupons(Request $request): JsonResponse
    {
        return response()->json([
            'coupons' => $request->user()->vendor->coupons()->latest()->get()->map(fn (Coupon $coupon) => $this->couponPayload($coupon)),
        ]);
    }

    public function storeCoupon(Request $request): JsonResponse
    {
        $coupon = $request->user()->vendor->coupons()->create($this->couponData($request));

        return response()->json([
            'message' => 'Coupon created.',
            'coupon' => $this->couponPayload($coupon),
        ], 201);
    }

    public function updateCoupon(Request $request, Coupon $coupon): JsonResponse
    {
        $this->ensureCouponOwned($request, $coupon);
        $coupon->update($this->couponData($request, $coupon));

        return response()->json([
            'message' => 'Coupon updated.',
            'coupon' => $this->couponPayload($coupon->refresh()),
        ]);
    }

    public function destroyCoupon(Request $request, Coupon $coupon): JsonResponse
    {
        $this->ensureCouponOwned($request, $coupon);
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted.']);
    }

    public function promotions(Request $request): JsonResponse
    {
        return response()->json([
            'promotions' => $request->user()->vendor->promotions()->with('targetProduct')->latest()->get()
                ->map(fn (Promotion $promotion) => $this->promotionPayload($promotion)),
        ]);
    }

    public function storePromotion(Request $request): JsonResponse
    {
        $data = $this->promotionData($request);
        $promotion = $request->user()->vendor->promotions()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Promotion created.',
            'promotion' => $this->promotionPayload($promotion->load('targetProduct')),
        ], 201);
    }

    public function updatePromotion(
        Request $request,
        Promotion $promotion
    ): JsonResponse {
        $this->ensurePromotionOwned($request, $promotion);
        $promotion->update($this->promotionData($request, $promotion));

        return response()->json([
            'message' => 'Promotion updated.',
            'promotion' => $this->promotionPayload($promotion->refresh()->load('targetProduct')),
        ]);
    }

    public function destroyPromotion(Request $request, Promotion $promotion): JsonResponse
    {
        $this->ensurePromotionOwned($request, $promotion);
        if ($promotion->banner_image) {
            Storage::disk('public')->delete($promotion->banner_image);
        }
        $promotion->delete();

        return response()->json(['message' => 'Promotion deleted.']);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        $subscriptions = Subscription::with(['customer.user', 'product', 'variant'])
            ->where('vendor_id', $request->user()->vendor->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'subscriptions' => collect($subscriptions->items())
                ->map(fn (Subscription $subscription) => $this->subscriptionPayload($subscription)),
            'stock_requirements' => Subscription::query()
                ->selectRaw('product_id, product_variant_id, SUM(quantity) as required_quantity')
                ->with(['product', 'variant'])
                ->where('vendor_id', $request->user()->vendor->id)
                ->where('status', 'active')
                ->groupBy('product_id', 'product_variant_id')
                ->get()
                ->map(fn (Subscription $item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'variant_name' => $item->variant?->name,
                    'required_quantity' => (int) $item->required_quantity,
                ]),
            'pagination' => $this->pagination($subscriptions),
        ]);
    }

    public function scheduledOrders(
        Request $request,
        ScheduledOrderService $scheduled
    ): JsonResponse {
        $orders = $scheduled->vendorOrders(
            $request->user()->vendor->id,
            $request->only(['from', 'to', 'status']),
        );

        return response()->json([
            'orders' => collect($orders->items())->map(
                fn (Order $order) => $this->orderPayload($order->loadMissing($this->orderRelations()))
            ),
            'pagination' => $this->pagination($orders),
        ]);
    }

    public function reports(
        Request $request,
        ReportService $reports,
        AnalyticsService $analytics
    ): JsonResponse {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'order_status' => ['nullable', 'string', 'max:50'],
        ]);
        $vendor = $request->user()->vendor;
        $report = $reports->vendorPrivate($vendor, $filters);

        return response()->json([
            'report' => [
                'summary' => $report['summary'],
                'best_selling' => $report['best_selling'],
                'low_stock' => $report['low_stock']->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock_quantity' => (int) $product->stock_quantity,
                ]),
                'charts' => $analytics->vendorCharts($vendor->id, $filters),
            ],
        ]);
    }

    private function orderActionResponse(Order $order, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'order' => $this->orderPayload($order->refresh()->load($this->orderRelations())),
        ]);
    }

    private function orderPayload(Order $order): array
    {
        $customer = $order->customer?->user;

        return [
            ...(new OrderResource($order))->resolve(),
            'status' => $order->order_status,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ] : null,
        ];
    }

    private function profilePayload(Request $request): array
    {
        $user = $request->user()->refresh();
        $vendor = $user->vendor()->firstOrFail();

        return [
            'id' => $vendor->id,
            'shop_name' => $vendor->store_name,
            'owner_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $vendor->formatted_address ?: $vendor->address,
            'city' => $vendor->city,
            'district' => $vendor->district,
            'province' => $vendor->province,
            'latitude' => $vendor->latitude,
            'longitude' => $vendor->longitude,
            'business_registration_number' => $vendor->business_registration_no,
            'approval_status' => $vendor->status,
            'commission_rate' => (float) $vendor->commission_rate,
            'shop_logo' => $user->profile_photo ? url('storage/'.$user->profile_photo) : null,
        ];
    }

    private function payoutPayload(VendorPayoutRequest $payout): array
    {
        return [
            'id' => $payout->id,
            'amount' => (float) $payout->amount,
            'bank_name' => $payout->bank_name,
            'account_name' => $payout->account_name,
            'account_number' => $payout->account_number,
            'branch' => $payout->branch,
            'status' => $payout->status,
            'admin_note' => $payout->admin_note,
            'processed_at' => $payout->processed_at,
            'created_at' => $payout->created_at,
        ];
    }

    private function refundPayload(Refund $refund): array
    {
        return [
            'id' => $refund->id,
            'order_id' => $refund->order_id,
            'order_number' => $refund->order?->order_number,
            'customer_name' => $refund->order?->customer?->user?->name,
            'amount' => (float) $refund->amount,
            'reason' => $refund->reason,
            'status' => $refund->status,
            'vendor_note' => $refund->vendor_note,
            'vendor_responded_at' => $refund->vendor_responded_at,
            'requested_at' => $refund->requested_at,
        ];
    }

    private function couponData(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount_type' => ['required', 'in:fixed_amount,percentage,free_delivery'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['required', 'in:active,inactive,expired'],
        ]);

        return [
            ...$data,
            'type' => $data['discount_type'] === 'percentage' ? 'percentage' : 'fixed',
            'value' => $data['discount_value'],
            'max_discount_amount' => $data['maximum_discount_amount'] ?? null,
        ];
    }

    private function couponPayload(Coupon $coupon): array
    {
        return [
            ...$coupon->only([
                'id', 'code', 'title', 'description', 'discount_type',
                'usage_limit', 'used_count', 'per_customer_limit', 'status',
            ]),
            'discount_value' => (float) $coupon->discount_value,
            'minimum_order_amount' => (float) $coupon->minimum_order_amount,
            'maximum_discount_amount' => $coupon->maximum_discount_amount !== null
                ? (float) $coupon->maximum_discount_amount
                : null,
            'starts_at' => $coupon->starts_at,
            'expires_at' => $coupon->expires_at,
        ];
    }

    private function promotionData(Request $request, ?Promotion $promotion = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'promotion_type' => ['required', 'in:flash_sale,seasonal_offer,featured_offer,clearance_sale'],
            'target_id' => ['required', 'integer', 'exists:products,id'],
            'discount_type' => ['required', 'in:fixed_amount,percentage'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['required', 'in:active,inactive,expired'],
        ]);
        abort_unless(
            $request->user()->vendor->products()->whereKey($data['target_id'])->exists(),
            422,
            'Promotions may target only your products.',
        );
        $data['target_type'] = 'product';
        if ($request->hasFile('banner_image')) {
            if ($promotion?->banner_image) {
                Storage::disk('public')->delete($promotion->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('promotions', 'public');
        } else {
            unset($data['banner_image']);
        }

        return $data;
    }

    private function promotionPayload(Promotion $promotion): array
    {
        return [
            ...$promotion->only([
                'id', 'title', 'description', 'promotion_type', 'target_type',
                'target_id', 'discount_type', 'status', 'views_count', 'clicks_count',
            ]),
            'product_name' => $promotion->targetProduct?->name,
            'discount_value' => (float) $promotion->discount_value,
            'banner_image' => $promotion->banner_image ? url('storage/'.$promotion->banner_image) : null,
            'starts_at' => $promotion->starts_at,
            'ends_at' => $promotion->ends_at,
        ];
    }

    private function subscriptionPayload(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'customer_name' => $subscription->customer?->user?->name,
            'product_id' => $subscription->product_id,
            'product_name' => $subscription->product?->name,
            'variant_name' => $subscription->variant?->name,
            'frequency' => $subscription->frequency,
            'quantity' => $subscription->quantity,
            'total_amount' => (float) $subscription->total_amount,
            'next_delivery_date' => $subscription->next_delivery_date,
            'status' => $subscription->status,
        ];
    }

    private function orderRelations(): array
    {
        return [
            'customer.user',
            'items.product',
            'items.variant',
            'payment',
            'delivery.rider.user',
            'statusHistories',
        ];
    }

    private function ensureOrderOwned(Request $request, Order $order): void
    {
        abort_unless($order->vendor_id === $request->user()->vendor?->id, 403);
    }

    private function ensureCouponOwned(Request $request, Coupon $coupon): void
    {
        abort_unless($coupon->vendor_id === $request->user()->vendor?->id, 403);
    }

    private function ensurePromotionOwned(Request $request, Promotion $promotion): void
    {
        abort_unless($promotion->vendor_id === $request->user()->vendor?->id, 403);
    }

    private function pagination($paginator): array
    {
        return [
            'total' => $paginator->total(),
            'count' => $paginator->count(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
        ];
    }
}
