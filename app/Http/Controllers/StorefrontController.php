<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $stores = VendorProfile::query()
            ->publiclyVisible()
            ->with('vendor')
            ->withCount('followers')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('vendor', fn ($vendor) => $vendor->where('store_name', 'like', '%'.$request->string('search').'%'));
            })
            ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('vendor.products', function ($products) use ($request) {
                    $products->visibleToCustomers()->where('category_id', $request->integer('category'));
                });
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('stores.index', [
            'stores' => $stores,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, VendorProfile $store): View
    {
        abort_unless(VendorProfile::publiclyVisible()->whereKey($store->id)->exists(), 404);

        $store->load([
            'vendor.user',
            'banners' => fn ($banners) => $banners->active(),
        ])->loadCount('followers');

        $products = Product::query()
            ->visibleToCustomers()
            ->where('vendor_id', $store->vendor_id)
            ->with(['category', 'vendor', 'images'])
            ->withAvg(['reviews as visible_reviews_avg_rating' => fn ($query) => $query->where('status', 'visible')], 'rating')
            ->latest()
            ->paginate(12, ['*'], 'products')
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->whereHas('products', fn ($query) => $query->visibleToCustomers()->where('vendor_id', $store->vendor_id))
            ->orderBy('name')
            ->get();
        $reviews = $store->vendor->reviews()
            ->with('customer.user')
            ->where('status', 'visible')
            ->latest()
            ->limit(6)
            ->get();
        $offers = $store->vendor->promotions()
            ->visibleOnStorefront()
            ->latest()
            ->limit(6)
            ->get();
        $rating = round((float) $store->vendor->reviews()->where('status', 'visible')->avg('rating'), 1);
        $isFollowing = $request->user()?->customer
            ? $store->followers()->whereKey($request->user()->customer->id)->exists()
            : false;

        return view('stores.show', compact('store', 'products', 'categories', 'reviews', 'offers', 'rating', 'isFollowing'));
    }
}
