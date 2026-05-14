<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminOrderStatusRequest;
use App\Http\Requests\AssignRiderRequest;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Vendor;
use App\Services\DeliveryService;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['customer.user', 'vendor', 'delivery.rider.user', 'payment'])
            ->when($request->filled('status'), fn ($query) => $query->where('order_status', $request->status))
            ->when($request->filled('vendor_id'), fn ($query) => $query->where('vendor_id', $request->vendor_id))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->customer_id))
            ->when($request->filled('rider_id'), fn ($query) => $query->whereHas('delivery', fn ($delivery) => $delivery->where('rider_id', $request->rider_id)))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('placed_at', $request->date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'vendors' => Vendor::orderBy('store_name')->get(),
            'riders' => Rider::with('user')->where('verification_status', 'verified')->get(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['customer.user', 'vendor', 'items.product', 'delivery.rider.user', 'payment']),
        ]);
    }

    public function assignRiderForm(Order $order): View
    {
        return view('admin.orders.assign-rider', [
            'order' => $order->load(['vendor', 'customer.user', 'delivery']),
            'riders' => Rider::with('user')->where('verification_status', 'verified')->get(),
        ]);
    }

    public function assignRider(AssignRiderRequest $request, Order $order, DeliveryService $deliveries): RedirectResponse
    {
        $deliveries->assignRider($order, Rider::findOrFail($request->rider_id));

        return redirect()->route('admin.orders.show', $order)->with('status', 'Rider assigned.');
    }

    public function status(AdminOrderStatusRequest $request, Order $order, OrderStatusService $orders): RedirectResponse
    {
        $orders->adminUpdate($order, $request->order_status);

        return back()->with('status', 'Order status updated.');
    }
}
