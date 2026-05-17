<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionRequest;
use App\Models\Product;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = $request->user()->customer->subscriptions()
            ->with(['product', 'variant', 'vendor', 'generatedOrders'])
            ->latest()
            ->paginate(15);

        return view('customer.subscriptions.index', compact('subscriptions'));
    }

    public function create(): View
    {
        $products = Product::with([
            'vendor',
            'variants' => fn ($query) => $query->where('status', 'active')->orderBy('name'),
        ])
            ->where('status', 'approved')
            ->where('is_subscription_eligible', true)
            ->orderBy('name')
            ->get();

        $subscriptionOptions = $products->flatMap(function (Product $product) {
            $basePrice = $product->discount_price ?: $product->price;
            $options = collect([[
                'value' => $product->id.':base',
                'product_id' => $product->id,
                'variant_id' => null,
                'label' => $product->name,
                'price' => $basePrice,
            ]]);

            return $options->merge($product->variants->map(fn ($variant) => [
                'value' => $product->id.':'.$variant->id,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'label' => $product->name.' - '.$variant->name,
                'price' => $variant->price,
            ]));
        })->values();

        return view('customer.subscriptions.create', [
            'products' => $products,
            'subscriptionOptions' => $subscriptionOptions,
        ]);
    }

    public function store(StoreSubscriptionRequest $request, SubscriptionService $subscriptions): RedirectResponse
    {
        $subscription = $subscriptions->create($request->user()->customer, $request->validated());

        return redirect()->route('customer.subscriptions.show', $subscription)->with('status', 'Subscription created successfully.');
    }

    public function show(Request $request, Subscription $subscription): View
    {
        abort_unless($subscription->customer_id === $request->user()->customer?->id, 403);

        return view('customer.subscriptions.show', [
            'subscription' => $subscription->load(['product', 'variant', 'vendor', 'generatedOrders.payment', 'generatedOrders.delivery']),
        ]);
    }

    public function edit(Request $request, Subscription $subscription): View
    {
        abort_unless($subscription->customer_id === $request->user()->customer?->id, 403);

        return view('customer.subscriptions.edit', compact('subscription'));
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        abort_unless($subscription->customer_id === $request->user()->customer?->id, 403);
        $subscriptions->update($subscription->load(['product', 'variant']), $request->validated());

        return redirect()->route('customer.subscriptions.show', $subscription)->with('status', 'Subscription updated successfully.');
    }

    public function pause(Request $request, Subscription $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        abort_unless($subscription->customer_id === $request->user()->customer?->id, 403);
        $subscriptions->pause($subscription->load(['customer.user']));

        return back()->with('status', 'Subscription paused.');
    }

    public function resume(Request $request, Subscription $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        abort_unless($subscription->customer_id === $request->user()->customer?->id, 403);
        $subscriptions->resume($subscription->load(['customer.user', 'product', 'variant']));

        return back()->with('status', 'Subscription resumed.');
    }

    public function cancel(Request $request, Subscription $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        abort_unless($subscription->customer_id === $request->user()->customer?->id, 403);
        $subscriptions->cancel($subscription->load(['customer.user']));

        return back()->with('status', 'Subscription cancelled.');
    }

    public function upcoming(Request $request): View
    {
        $subscriptions = $request->user()->customer->subscriptions()
            ->with(['product', 'variant', 'vendor'])
            ->where('status', 'active')
            ->whereNotNull('next_delivery_date')
            ->orderBy('next_delivery_date')
            ->paginate(15);

        return view('customer.subscriptions.upcoming', compact('subscriptions'));
    }
}
