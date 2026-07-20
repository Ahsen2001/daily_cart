<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function index(Request $request): JsonResponse
    {
        $wishlist = $request->user()->customer->wishlists()
            ->with('product')
            ->latest()
            ->get()
            ->filter(fn (Wishlist $item) => $item->product && Product::visibleToCustomers()->whereKey($item->product_id)->exists())
            ->map(fn (Wishlist $item) => [
                'id' => $item->id,
                'wishlist_id' => $item->id,
                'product' => new ProductResource($item->product),
            ])->values();

        return response()->json(['wishlist' => $wishlist]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['product_id' => ['required', 'integer']]);
        $product = Product::visibleToCustomers()->findOrFail($validated['product_id']);
        $item = $request->user()->customer->wishlists()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => 'Product added to wishlist.',
            'wishlist' => [
                'id' => $item->id,
                'product' => new ProductResource($product),
            ],
        ], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->customer->wishlists()->where('product_id', $product->id)->delete();

        return response()->json(['message' => 'Product removed from wishlist.']);
    }

    public function moveToCart(Request $request, Product $product): JsonResponse
    {
        abort_unless(Product::visibleToCustomers()->whereKey($product->id)->exists(), 404);
        $customer = $request->user()->customer;
        $this->carts->add($customer, $product, 1);
        $customer->wishlists()->where('product_id', $product->id)->delete();

        return response()->json(['message' => 'Product moved to cart.']);
    }
}
