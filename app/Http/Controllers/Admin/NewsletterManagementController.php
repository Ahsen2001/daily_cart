<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterManagementController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = NewsletterSubscription::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), fn ($query) => $query->where('email', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.newsletter.index', compact('subscriptions'));
    }

    public function update(Request $request, NewsletterSubscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,unsubscribed'],
        ]);

        $subscription->update($validated);

        return back()->with('status', 'Newsletter subscription updated.');
    }
}
