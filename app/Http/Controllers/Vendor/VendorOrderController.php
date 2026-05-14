<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorOrderController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;

        $orders = Order::query()
            ->with(['customer.user', 'delivery.rider.user', 'payment'])
            ->where('vendor_id', $vendor->id)
            ->when($request->filled('status'), fn ($query) => $query->where('order_status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('vendor.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('manage', $order);

        return view('vendor.orders.show', [
            'order' => $order->load(['customer.user', 'items.product', 'delivery.rider.user', 'payment']),
        ]);
    }

    public function confirm(Order $order, OrderStatusService $orders): RedirectResponse
    {
        $this->authorize('manage', $order);
        $orders->confirm($order);

        return back()->with('status', 'Order confirmed.');
    }

    public function packed(Order $order, OrderStatusService $orders): RedirectResponse
    {
        $this->authorize('manage', $order);
        $orders->pack($order);

        return back()->with('status', 'Order marked as packed.');
    }

    public function cancel(CancelOrderRequest $request, Order $order, OrderStatusService $orders): RedirectResponse
    {
        $this->authorize('manage', $order);
        $orders->cancel($order, $request->reason, 'Vendor can cancel only pending or confirmed orders.');

        return back()->with('status', 'Order cancelled.');
    }

    public function earnings(Request $request): View
    {
        $vendor = $request->user()->vendor;
        $completedOrders = $vendor->orders()->where('order_status', 'delivered')->latest()->paginate(15);
        $total = (float) $vendor->orders()->where('order_status', 'delivered')->sum('total_amount');

        return view('vendor.orders.earnings', compact('completedOrders', 'total'));
    }
}
