<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\ScheduledOrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor?->status === 'approved', 403);

        return view('vendor.subscriptions.index', [
            'subscriptions' => Subscription::with(['customer.user', 'product', 'variant', 'generatedOrders'])
                ->where('vendor_id', $vendor->id)
                ->latest()
                ->paginate(15),
            'stockRequirements' => Subscription::query()
                ->selectRaw('product_id, product_variant_id, SUM(quantity) as required_quantity')
                ->with(['product', 'variant'])
                ->where('vendor_id', $vendor->id)
                ->where('status', 'active')
                ->groupBy('product_id', 'product_variant_id')
                ->get(),
        ]);
    }

    public function scheduledOrders(Request $request, ScheduledOrderService $scheduledOrders): View
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor?->status === 'approved', 403);

        return view('vendor.subscriptions.scheduled-orders', [
            'orders' => $scheduledOrders->vendorOrders($vendor->id, $request->only(['from', 'to', 'status'])),
        ]);
    }
}
