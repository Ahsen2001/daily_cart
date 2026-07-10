<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\CartService;
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

        $orders = Order::where('customer_id', $customer->id)
            ->with('statusHistories')
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

        $order->load('statusHistories');

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }

    public function quote(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $cart = $this->cartService->activeCart($customer);

        $quote = $this->orderService->quote(
            $cart,
            $request->coupon_code,
            $customer,
            (int) $request->input('loyalty_points', 0)
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
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'delivery_address' => ['required', 'string'],
            'delivery_latitude' => ['nullable', 'numeric'],
            'delivery_longitude' => ['nullable', 'numeric'],
            'delivery_distance_meters' => ['nullable', 'integer'],
            'payment_method' => ['required', 'string', 'in:cash_on_delivery,online_payment'],
            'scheduled_delivery_at' => ['required', 'date'],
            'coupon_code' => ['nullable', 'string'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
        ]);

        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $orders = $this->orderService->createFromCart($customer, $request->all());

        return response()->json([
            'message' => 'Orders placed successfully.',
            'orders' => OrderResource::collection($orders),
        ], 201);
    }
}
