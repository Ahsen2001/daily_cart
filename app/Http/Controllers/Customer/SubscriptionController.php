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
            ->with(['product', 'vendor', 'generatedOrders'])
            ->latest()
            ->paginate(15);

        return view('customer.subscriptions.index', compact('subscriptions'));
    }

    public function create(): View
    {
        return view('customer.subscriptions.create', [
            'products' => Product::with('vendor')
                ->where('status', 'approved')
                ->where('is_subscription_eligible', true)
                ->orderBy('name')
                ->get(),
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
            'subscription' => $subscription->load(['product', 'vendor', 'generatedOrders.payment', 'generatedOrders.delivery']),
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
        $subscriptions->update($subscription->load('product'), $request->validated());

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
        $subscriptions->resume($subscription->load(['customer.user', 'product']));

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
            ->with(['product', 'vendor'])
            ->where('status', 'active')
            ->whereNotNull('next_delivery_date')
            ->orderBy('next_delivery_date')
            ->paginate(15);

        return view('customer.subscriptions.upcoming', compact('subscriptions'));
    }
}
