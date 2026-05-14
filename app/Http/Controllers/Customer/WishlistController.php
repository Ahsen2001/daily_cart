<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.wishlist.index', [
            'wishlists' => $request->user()->customer->wishlists()->with('product.category')->latest()->paginate(15),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'approved' && $product->category?->status === 'active', 404);

        $request->user()->customer->wishlists()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return back()->with('status', 'Product added to wishlist.');
    }

    public function destroy(Request $request, Wishlist $wishlist): RedirectResponse
    {
        abort_unless($request->user()->customer?->id === $wishlist->customer_id, 403);

        $wishlist->delete();

        return back()->with('status', 'Wishlist item removed.');
    }

    public function moveToCart(Request $request, Wishlist $wishlist, CartService $cartService): RedirectResponse
    {
        abort_unless($request->user()->customer?->id === $wishlist->customer_id, 403);

        $cartService->add($request->user()->customer, $wishlist->product->load('category'), 1);
        $wishlist->delete();

        return redirect()->route('customer.cart.index')->with('status', 'Wishlist item moved to cart.');
    }
}
