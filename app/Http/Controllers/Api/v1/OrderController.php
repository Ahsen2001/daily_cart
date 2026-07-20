<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $orders = Order::query()->where('customer_id', $customer->id)
            ->with($this->orderRelations())
            ->latest()
            ->paginate(15);

        return response()->json([
            'orders' => OrderResource::collection($orders),
            'pagination' => [
                'total' => $orders->total(),
                'count' => $orders->count(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'total_pages' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Order $order, Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer || $order->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $order->load($this->orderRelations());

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }

    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:255'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
            'delivery_district' => ['nullable', 'string', 'max:255'],
            'delivery_distance_meters' => ['nullable', 'integer', 'min:0'],
        ]);

        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $cart = $this->cartService->activeCart($customer);

        $quote = $this->orderService->quote(
            $cart,
            $validated['coupon_code'] ?? null,
            $customer,
            (int) ($validated['loyalty_points'] ?? 0),
            $validated['delivery_district'] ?? null,
            isset($validated['delivery_distance_meters']) ? (int) $validated['delivery_distance_meters'] : null,
        );

        return response()->json([
            'quote' => [
                'subtotal' => (float) $quote['subtotal'],
                'discount' => (float) $quote['discount'],
                'loyalty_points' => (int) $quote['loyalty_points'],
                'loyalty_discount' => (float) $quote['loyalty_discount'],
                'delivery_fee' => (float) $quote['delivery_fee'],
                'service_charge' => (float) $quote['service_charge'],
                'grand_total' => (float) $quote['grand_total'],
                'estimated_delivery_minutes' => $quote['estimated_delivery_minutes'],
                'free_delivery_eligible' => (bool) $quote['free_delivery_eligible'],
                'delivery_rule_scope' => $quote['delivery_rule_scope'],
            ],
        ]);
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $orders = collect($this->orderService->createFromCart($customer, $request->validated()))
            ->map(fn (Order $order) => $order->load($this->orderRelations()));

        return response()->json([
            'message' => 'Orders placed successfully.',
            'orders' => OrderResource::collection($orders),
        ], 201);
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
