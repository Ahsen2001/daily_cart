<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ]);

        NewsletterSubscription::updateOrCreate(
            ['email' => strtolower($validated['email'])],
            [
                'status' => 'active',
                'subscribed_at' => now(),
            ]
        );

        return back()->with('newsletter_status', 'Thank you for joining the DailyCart newsletter.');
    }
}
