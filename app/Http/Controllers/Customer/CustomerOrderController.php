<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->customer->orders()
            ->with(['vendor', 'delivery.rider.user', 'payment'])
            ->latest()
            ->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        return view('customer.orders.show', [
            'order' => $order->load(['items.product', 'vendor', 'payment', 'delivery.rider.user', 'delivery.proofs']),
        ]);
    }

    public function receipt(Request $request, Order $order): View
    {
        $this->authorize('view', $order);
        abort_unless($order->customer_id === $request->user()->customer?->id, 403);
        abort_unless($order->order_status === 'delivered', 403, 'Receipt is available after delivery is completed.');

        return view('customer.orders.receipt', [
            'order' => $order->load(['customer.user', 'items.product', 'vendor', 'payment', 'delivery.rider.user']),
        ]);
    }

    public function cancel(CancelOrderRequest $request, Order $order, OrderStatusService $orders): RedirectResponse
    {
        $orders->cancel($order, $request->reason, 'Customer can cancel only pending orders.');

        return back()->with('status', 'Order cancelled.');
    }
}
