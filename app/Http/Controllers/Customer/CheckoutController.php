<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\DeliveryScheduleService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, CartService $cartService, OrderService $orderService, DeliveryScheduleService $schedule): View
    {
        $cart = $cartService->activeCart($request->user()->customer)->load(['items.product.category', 'items.variant']);
        $couponCode = session('checkout_coupon_code');
        $loyaltyPoints = (int) session('checkout_loyalty_points', 0);

        return view('customer.checkout.show', [
            'cart' => $cart,
            'quote' => $orderService->quote($cart, $couponCode, $request->user()->customer, $loyaltyPoints),
            'couponCode' => $couponCode,
            'loyaltyPoints' => $loyaltyPoints,
            'minimumDeliveryTime' => $schedule->minimumDeliveryTime(),
            'currentDateTime' => now(),
            'googleMapsBrowserKey' => config('services.google_maps.browser_key'),
        ]);
    }

    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        session(['checkout_coupon_code' => $request->coupon_code]);

        return redirect()->route('customer.checkout.show')->with('status', 'Coupon updated.');
    }

    public function applyLoyalty(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
        ]);

        session(['checkout_loyalty_points' => (int) ($validated['loyalty_points'] ?? 0)]);

        return redirect()->route('customer.checkout.show')->with('status', 'Loyalty points updated.');
    }

    public function store(CheckoutRequest $request, OrderService $orderService): RedirectResponse
    {
        $data = $request->validated();
        $data['coupon_code'] = $data['coupon_code'] ?: session('checkout_coupon_code');
        $data['loyalty_points'] = $data['loyalty_points'] ?? session('checkout_loyalty_points', 0);

        $orders = $orderService->createFromCart($request->user()->customer, $data);

        session()->forget(['checkout_coupon_code', 'checkout_loyalty_points']);
        session(['placed_order_ids' => collect($orders)->pluck('id')->all()]);

        return redirect()->route('customer.checkout.success');
    }

    public function success(Request $request): View
    {
        $orders = Order::query()
            ->whereIn('id', session('placed_order_ids', []))
            ->where('customer_id', $request->user()->customer->id)
            ->with(['items', 'payment'])
            ->latest()
            ->get();

        return view('customer.checkout.success', compact('orders'));
    }
}
