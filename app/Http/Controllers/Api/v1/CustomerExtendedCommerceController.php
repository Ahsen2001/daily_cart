<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Subscription;
use App\Services\RefundService;
use App\Services\ScheduledOrderService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerExtendedCommerceController extends Controller
{
    public function wallet(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;

        return response()->json([
            'wallet' => [
                'balance' => (float) $customer->wallet_balance,
                'currency' => 'LKR',
            ],
            'transactions' => $request->user()->walletTransactions()
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($transaction) => $this->walletTransactionPayload($transaction)),
        ]);
    }

    public function walletTransactions(Request $request): JsonResponse
    {
        $transactions = $request->user()->walletTransactions()->latest()->paginate(20);

        return response()->json([
            'transactions' => collect($transactions->items())
                ->map(fn ($transaction) => $this->walletTransactionPayload($transaction)),
            'pagination' => $this->pagination($transactions),
        ]);
    }

    public function refunds(Request $request): JsonResponse
    {
        $refunds = Refund::query()
            ->whereHas('order', fn ($query) => $query->where('customer_id', $request->user()->customer->id))
            ->with(['order', 'payment'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'refunds' => collect($refunds->items())->map(fn (Refund $refund) => $this->refundPayload($refund)),
            'pagination' => $this->pagination($refunds),
        ]);
    }

    public function refund(Request $request, Refund $refund): JsonResponse
    {
        $this->authorize('view', $refund);

        return response()->json([
            'refund' => $this->refundPayload($refund->load(['order', 'payment'])),
        ]);
    }

    public function requestRefund(
        Request $request,
        Order $order,
        RefundService $refunds
    ): JsonResponse {
        $this->authorize('createForOrder', [Refund::class, $order]);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $refund = $refunds->request($order->load(['payment', 'refunds', 'customer.user']), (float) $validated['amount'], $validated['reason']);

        return response()->json([
            'message' => 'Refund request submitted.',
            'refund' => $this->refundPayload($refund->load(['order', 'payment'])),
        ], 201);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        $subscriptions = $request->user()->customer->subscriptions()
            ->with(['product', 'variant', 'vendor'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'subscriptions' => collect($subscriptions->items())
                ->map(fn (Subscription $subscription) => $this->subscriptionPayload($subscription)),
            'pagination' => $this->pagination($subscriptions),
        ]);
    }

    public function subscription(Request $request, Subscription $subscription): JsonResponse
    {
        $this->ensureSubscriptionOwned($request, $subscription);

        return response()->json([
            'subscription' => $this->subscriptionPayload(
                $subscription->load(['product', 'variant', 'vendor', 'generatedOrders.statusHistories'])
            ),
        ]);
    }

    public function createSubscription(
        Request $request,
        SubscriptionService $subscriptions
    ): JsonResponse {
        $validated = $this->validateSubscription($request, true);
        $subscription = $subscriptions->create($request->user()->customer, $validated);

        return response()->json([
            'message' => 'Subscription created successfully.',
            'subscription' => $this->subscriptionPayload(
                $subscription->load(['product', 'variant', 'vendor'])
            ),
        ], 201);
    }

    public function updateSubscription(
        Request $request,
        Subscription $subscription,
        SubscriptionService $subscriptions
    ): JsonResponse {
        $this->ensureSubscriptionOwned($request, $subscription);
        $validated = $this->validateSubscription($request, false);
        $subscription = $subscriptions->update($subscription->load(['product', 'variant']), $validated);

        return response()->json([
            'message' => 'Subscription updated successfully.',
            'subscription' => $this->subscriptionPayload(
                $subscription->load(['product', 'variant', 'vendor'])
            ),
        ]);
    }

    public function pauseSubscription(
        Request $request,
        Subscription $subscription,
        SubscriptionService $subscriptions
    ): JsonResponse {
        return $this->changeSubscriptionStatus($request, $subscription, $subscriptions, 'pause');
    }

    public function resumeSubscription(
        Request $request,
        Subscription $subscription,
        SubscriptionService $subscriptions
    ): JsonResponse {
        return $this->changeSubscriptionStatus($request, $subscription, $subscriptions, 'resume');
    }

    public function cancelSubscription(
        Request $request,
        Subscription $subscription,
        SubscriptionService $subscriptions
    ): JsonResponse {
        return $this->changeSubscriptionStatus($request, $subscription, $subscriptions, 'cancel');
    }

    public function upcomingSubscriptions(Request $request): JsonResponse
    {
        $subscriptions = $request->user()->customer->subscriptions()
            ->with(['product', 'variant', 'vendor'])
            ->where('status', 'active')
            ->whereNotNull('next_delivery_date')
            ->orderBy('next_delivery_date')
            ->paginate(15);

        return response()->json([
            'subscriptions' => collect($subscriptions->items())
                ->map(fn (Subscription $subscription) => $this->subscriptionPayload($subscription)),
            'pagination' => $this->pagination($subscriptions),
        ]);
    }

    public function scheduledOrders(
        Request $request,
        ScheduledOrderService $scheduledOrders
    ): JsonResponse {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,confirmed,processing,out_for_delivery,delivered,cancelled'],
        ]);
        $orders = $scheduledOrders->customerOrders($request->user()->customer, $filters);

        return response()->json([
            'orders' => OrderResource::collection(collect($orders->items())),
            'pagination' => $this->pagination($orders),
        ]);
    }

    public function cancelScheduledOrder(
        Request $request,
        Order $order,
        ScheduledOrderService $scheduledOrders
    ): JsonResponse {
        $order = $scheduledOrders->cancelPending($order, $request->user()->customer);

        return response()->json([
            'message' => 'Scheduled order cancelled.',
            'order' => new OrderResource($order->load('statusHistories')),
        ]);
    }

    public function policies(): JsonResponse
    {
        return response()->json([
            'policies' => [
                ['key' => 'privacy', 'title' => 'Privacy Policy', 'url' => url('/privacy-policy')],
                ['key' => 'terms', 'title' => 'Terms & Conditions', 'url' => url('/terms-and-conditions')],
                ['key' => 'refund', 'title' => 'Refund Policy', 'url' => url('/refund-policy')],
            ],
        ]);
    }

    private function validateSubscription(Request $request, bool $creating): array
    {
        $common = [
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'preferred_delivery_time' => ['required', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', $creating ? 'after_or_equal:start_date' : 'after_or_equal:today'],
            'payment_method' => ['required', 'in:cash_on_delivery,card,bank_transfer,wallet'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($creating) {
            $common = [
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
                'frequency' => ['required', 'in:daily,weekly,monthly'],
                'start_date' => ['required', 'date', 'after_or_equal:today'],
                ...$common,
            ];
        }

        return $request->validate($common);
    }

    private function changeSubscriptionStatus(
        Request $request,
        Subscription $subscription,
        SubscriptionService $subscriptions,
        string $action
    ): JsonResponse {
        $this->ensureSubscriptionOwned($request, $subscription);
        $subscription = $subscriptions->{$action}(
            $subscription->load(['customer.user', 'product', 'variant'])
        );

        return response()->json([
            'message' => match ($action) {
                'pause' => 'Subscription paused successfully.',
                'resume' => 'Subscription resumed successfully.',
                default => 'Subscription cancelled successfully.',
            },
            'subscription' => $this->subscriptionPayload(
                $subscription->load(['product', 'variant', 'vendor'])
            ),
        ]);
    }

    private function walletTransactionPayload($transaction): array
    {
        return [
            'id' => $transaction->id,
            'transaction_type' => $transaction->transaction_type,
            'type' => $transaction->type,
            'source' => $transaction->source,
            'amount' => (float) $transaction->amount,
            'balance_after' => (float) $transaction->balance_after,
            'currency' => $transaction->currency,
            'reference' => $transaction->reference,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at,
        ];
    }

    private function refundPayload(Refund $refund): array
    {
        return [
            'id' => $refund->id,
            'order_id' => $refund->order_id,
            'order_number' => $refund->order?->order_number,
            'amount' => (float) $refund->amount,
            'refund_method' => $refund->refund_method,
            'reason' => $refund->reason,
            'admin_note' => $refund->admin_note,
            'status' => $refund->status,
            'requested_at' => $refund->requested_at,
            'processed_at' => $refund->processed_at,
        ];
    }

    private function subscriptionPayload(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'product_id' => $subscription->product_id,
            'product_name' => $subscription->product?->name,
            'product_image' => $subscription->product?->image_url,
            'product_variant_id' => $subscription->product_variant_id,
            'variant_name' => $subscription->variant?->name,
            'vendor_id' => $subscription->vendor_id,
            'vendor_name' => $subscription->vendor?->store_name,
            'frequency' => $subscription->frequency,
            'quantity' => $subscription->quantity,
            'unit_price' => (float) $subscription->unit_price,
            'total_amount' => (float) $subscription->total_amount,
            'currency' => $subscription->currency,
            'delivery_address' => $subscription->delivery_address,
            'preferred_delivery_time' => $subscription->preferred_delivery_time,
            'start_date' => $subscription->start_date,
            'end_date' => $subscription->end_date,
            'next_delivery_date' => $subscription->next_delivery_date,
            'payment_method' => $subscription->payment_method,
            'notes' => $subscription->notes,
            'status' => $subscription->status,
            'orders' => $subscription->relationLoaded('generatedOrders')
                ? OrderResource::collection($subscription->generatedOrders)
                : [],
        ];
    }

    private function ensureSubscriptionOwned(Request $request, Subscription $subscription): void
    {
        abort_unless($subscription->customer_id === $request->user()->customer?->id, 403);
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
