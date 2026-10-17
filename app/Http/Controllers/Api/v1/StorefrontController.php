<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\VendorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorefrontController extends Controller
{
    public function home(): JsonResponse
    {
        $base = fn () => VendorProfile::query()
            ->publiclyVisible()
            ->with('vendor')
            ->withCount('followers');

        $featured = $base()
            ->where('is_featured', true)
            ->latest()
            ->limit(8)
            ->get();
        $popular = $base()
            ->orderByDesc('followers_count')
            ->latest()
            ->limit(8)
            ->get();

        return response()->json([
            'offers' => $this->offersQuery()->limit(8)->get()->map(fn (Promotion $promotion) => $this->promotionPayload($promotion)),
            'featured_stores' => $featured->map(fn (VendorProfile $store) => $this->storePayload($store)),
            'popular_stores' => $popular->map(fn (VendorProfile $store) => $this->storePayload($store)),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $stores = VendorProfile::query()
            ->publiclyVisible()
            ->with('vendor')
            ->withCount('followers')
            ->when(filled($validated['search'] ?? null), function ($query) use ($validated) {
                $search = $validated['search'];
                $query->where(function ($storeQuery) use ($search) {
                    $storeQuery->where('description', 'like', '%'.$search.'%')
                        ->orWhereHas('vendor', fn ($vendor) => $vendor->where('store_name', 'like', '%'.$search.'%'));
                });
            })
            ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
            ->when(isset($validated['category_id']), function ($query) use ($validated) {
                $query->whereHas('vendor.products', fn ($products) => $products
                    ->visibleToCustomers()
                    ->where('category_id', $validated['category_id']));
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('followers_count')
            ->latest('vendor_profiles.id')
            ->paginate(12);

        return response()->json([
            'stores' => $stores->getCollection()->map(fn (VendorProfile $store) => $this->storePayload($store)),
            'pagination' => [
                'total' => $stores->total(),
                'count' => $stores->count(),
                'per_page' => $stores->perPage(),
                'current_page' => $stores->currentPage(),
                'total_pages' => $stores->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, VendorProfile $store): JsonResponse
    {
        abort_unless(VendorProfile::publiclyVisible()->whereKey($store->id)->exists(), 404);

        $store->load([
            'vendor.user',
            'banners' => fn ($banners) => $banners->active(),
        ])->loadCount('followers');

        $products = Product::query()
            ->visibleToCustomers()
            ->where('vendor_id', $store->vendor_id)
            ->with(['category', 'vendor', 'images', 'brandRelation'])
            ->withAvg(['reviews as visible_reviews_avg_rating' => fn ($reviews) => $reviews->where('status', 'visible')], 'rating')
            ->latest()
            ->limit(24)
            ->get();
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
        $offers = $this->offersQuery()
            ->where('vendor_id', $store->vendor_id)
            ->limit(6)
            ->get();
        $customer = $request->user('sanctum')?->customer;

        return response()->json([
            'store' => array_merge($this->storePayload($store), [
                'opening_hours' => $store->opening_hours ?? [],
                'contact_phone' => $store->contact_phone,
                'contact_email' => $store->contact_email,
                'address' => $store->vendor?->formatted_address ?: $store->vendor?->address,
                'city' => $store->vendor?->city,
                'district' => $store->vendor?->district,
                'rating' => round((float) $store->vendor->reviews()->where('status', 'visible')->avg('rating'), 1),
                'is_following' => $customer
                    ? $store->followers()->whereKey($customer->id)->exists()
                    : false,
                'banners' => $store->banners->map(fn ($banner) => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image' => $banner->image_url,
                    'link_url' => $banner->link_url,
                ])->values(),
                'categories' => $categories->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->display_image_url,
                ])->values(),
                'products' => ProductResource::collection($products),
                'offers' => $offers->map(fn (Promotion $promotion) => $this->promotionPayload($promotion))->values(),
                'reviews' => $reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'customer_name' => $review->customer?->user?->name ?? 'Customer',
                    'rating' => (float) $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at?->toIso8601String(),
                ])->values(),
            ]),
        ]);
    }

    public function promotions(): JsonResponse
    {
        return response()->json([
            'promotions' => $this->offersQuery()->get()->map(fn (Promotion $promotion) => $this->promotionPayload($promotion)),
        ]);
    }

    public function promotion(Promotion $promotion): JsonResponse
    {
        abort_unless(Promotion::query()->visibleOnStorefront()->whereKey($promotion->id)->exists(), 404);
        $promotion->load(['vendor', 'targetProduct.category', 'targetProduct.images']);

        return response()->json(['promotion' => $this->promotionPayload($promotion)]);
    }

    public function follow(Request $request, VendorProfile $store): JsonResponse
    {
        abort_unless(VendorProfile::publiclyVisible()->whereKey($store->id)->exists(), 404);
        $request->user()->customer->followedStores()->syncWithoutDetaching([$store->id]);

        return response()->json(['message' => 'Store followed successfully.', 'is_following' => true]);
    }

    public function unfollow(Request $request, VendorProfile $store): JsonResponse
    {
        $request->user()->customer->followedStores()->detach($store->id);

        return response()->json(['message' => 'Store unfollowed successfully.', 'is_following' => false]);
    }

    private function offersQuery()
    {
        return Promotion::query()
            ->visibleOnStorefront()
            ->with(['vendor', 'targetProduct.category', 'targetProduct.images'])
            ->latest();
    }

    private function storePayload(VendorProfile $store): array
    {
        return [
            'id' => $store->id,
            'slug' => $store->slug,
            'name' => $store->vendor?->store_name ?? 'DailyCart Store',
            'description' => $store->description ?? '',
            'logo' => $store->logo_url,
            'cover_image' => $store->cover_image_url,
            'delivery_estimate' => $store->delivery_estimate,
            'minimum_order' => (float) ($store->minimum_order ?? 0),
            'is_featured' => (bool) $store->is_featured,
            'followers_count' => (int) ($store->followers_count ?? 0),
        ];
    }

    private function promotionPayload(Promotion $promotion): array
    {
        $product = $promotion->target_type === 'product' ? $promotion->targetProduct : null;
        $discount = $promotion->discount_type === 'percentage'
            ? rtrim(rtrim(number_format((float) $promotion->discount_value, 2), '0'), '.').'% OFF'
            : 'Rs. '.number_format((float) $promotion->discount_value, 2).' OFF';

        return [
            'id' => $promotion->id,
            'title' => $promotion->title,
            'description' => $promotion->description ?? '',
            'type' => $promotion->promotion_type,
            'target_type' => $promotion->target_type,
            'target_id' => $promotion->target_id,
            'product_id' => $product?->id,
            'vendor_name' => $promotion->vendor?->store_name,
            'image' => $promotion->banner_image
                ? url(Storage::url($promotion->banner_image))
                : ($product?->display_image_url ?? ''),
            'discount_text' => $discount,
            'starts_at' => $promotion->starts_at?->toIso8601String(),
            'ends_at' => $promotion->ends_at?->toIso8601String(),
            'is_active' => true,
        ];
    }
}
