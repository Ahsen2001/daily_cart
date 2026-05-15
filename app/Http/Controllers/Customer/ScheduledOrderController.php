<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\ScheduledOrderFilterRequest;
use App\Models\Order;
use App\Services\ScheduledOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduledOrderController extends Controller
{
    public function index(ScheduledOrderFilterRequest $request, ScheduledOrderService $scheduledOrders): View
    {
        return view('customer.scheduled-orders.index', [
            'orders' => $scheduledOrders->customerOrders($request->user()->customer, $request->validated()),
        ]);
    }

    public function admin(ScheduledOrderFilterRequest $request, ScheduledOrderService $scheduledOrders): View
    {
        abort_unless($request->user()->isAdminUser(), 403);

        return view('admin.subscriptions.scheduled-orders', [
            'orders' => $scheduledOrders->adminOrders($request->validated()),
        ]);
    }

    public function cancel(Request $request, Order $order, ScheduledOrderService $scheduledOrders): RedirectResponse
    {
        $scheduledOrders->cancelPending($order, $request->user()->customer);

        return back()->with('status', 'Scheduled order cancelled.');
    }
}
