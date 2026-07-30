<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminStorefrontController extends Controller
{
    public function index(): View
    {
        return view('admin.stores.index', [
            'stores' => VendorProfile::query()
                ->with(['vendor' => fn ($vendor) => $vendor->withCount([
                    'products as approved_products_count' => fn ($products) => $products->visibleToCustomers(),
                ])])
                ->withCount('followers')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function approve(Request $request, VendorProfile $store): RedirectResponse
    {
        $store->update([
            'profile_status' => 'approved',
            'is_enabled' => true,
            'approved_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Store profile approved.');
    }

    public function toggleFeatured(VendorProfile $store): RedirectResponse
    {
        $store->update(['is_featured' => ! $store->is_featured]);

        return back()->with('status', $store->is_featured ? 'Store featured on the homepage.' : 'Store removed from featured stores.');
    }

    public function toggleEnabled(VendorProfile $store): RedirectResponse
    {
        $store->update(['is_enabled' => ! $store->is_enabled]);

        return back()->with('status', $store->is_enabled ? 'Storefront enabled.' : 'Storefront disabled. Existing global product listings remain unchanged.');
    }

    public function analytics(VendorProfile $store): View
    {
        $vendor = $store->vendor;

        return view('admin.stores.analytics', [
            'store' => $store->load('vendor')->loadCount('followers'),
            'metrics' => [
                'approved_products' => $vendor->products()->visibleToCustomers()->count(),
                'followers' => $store->followers()->count(),
                'orders' => $vendor->orders()->count(),
                'revenue' => $vendor->orders()->whereNotIn('order_status', ['cancelled'])->sum('total_amount'),
                'average_rating' => round((float) $vendor->reviews()->where('status', 'visible')->avg('rating'), 1),
            ],
        ]);
    }
}
