<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->with(['user', 'addresses'])
            ->withCount(['orders', 'wishlists', 'supportTickets as support_tickets_count'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('user', function ($user) use ($request) {
                    $user->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('email', 'like', '%'.$request->search.'%')
                        ->orWhere('phone', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer): View
    {
        return view('admin.customers.show', [
            'customer' => $customer->load([
                'user',
                'addresses',
                'orders' => fn ($query) => $query->latest()->limit(10),
                'searchHistories' => fn ($query) => $query->latest('searched_at')->limit(10),
                'favoriteVendors',
            ]),
        ]);
    }

    public function updateStatus(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        $customer->update($validated);
        $customer->user?->update(['status' => $validated['status']]);

        return back()->with('status', 'Customer status updated.');
    }
}
