<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\AdminSubscriptionFilterRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Vendor;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriptionController extends Controller
{
    public function index(AdminSubscriptionFilterRequest $request): View
    {
        $filters = $request->validated();

        return view('admin.subscriptions.index', [
            'subscriptions' => Subscription::with(['customer.user', 'vendor', 'product', 'variant', 'generatedOrders'])
                ->when($filters['customer_id'] ?? null, fn ($query, $id) => $query->where('customer_id', $id))
                ->when($filters['vendor_id'] ?? null, fn ($query, $id) => $query->where('vendor_id', $id))
                ->when($filters['product_id'] ?? null, fn ($query, $id) => $query->where('product_id', $id))
                ->when($filters['frequency'] ?? null, fn ($query, $frequency) => $query->where('frequency', $frequency))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'failedCount' => Subscription::whereNotNull('failed_reason')->count(),
            'customers' => Customer::with('user:id,name')->get(),
            'vendors' => Vendor::orderBy('store_name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function pause(Subscription $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        $subscriptions->pause($subscription->load('customer.user'));

        return back()->with('status', 'Subscription paused.');
    }

    public function cancel(Subscription $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        $subscriptions->cancel($subscription->load('customer.user'));

        return back()->with('status', 'Subscription cancelled.');
    }

    public function eligibleProducts(): View
    {
        return view('admin.subscriptions.products', [
            'products' => Product::with(['vendor', 'category'])->orderBy('name')->paginate(20),
        ]);
    }

    public function updateEligibility(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['is_subscription_eligible' => ['required', 'boolean']]);
        $product->update(['is_subscription_eligible' => (bool) $request->boolean('is_subscription_eligible')]);

        return back()->with('status', 'Subscription eligibility updated.');
    }
}
