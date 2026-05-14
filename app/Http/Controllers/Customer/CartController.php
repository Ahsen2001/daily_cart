<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): View
    {
        $cart = $this->cartService->activeCart($request->user()->customer)
            ->load(['items.product.category', 'items.variant']);

        return view('customer.cart.index', [
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function store(AddToCartRequest $request, Product $product): RedirectResponse
    {
        $variant = $request->filled('product_variant_id')
            ? ProductVariant::where('product_id', $product->id)->findOrFail($request->product_variant_id)
            : null;

        $this->cartService->add($request->user()->customer, $product->load('category'), (int) $request->quantity, $variant);

        return redirect()->route('customer.cart.index')->with('status', 'Product added to cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $this->cartService->update($item->load(['product.category', 'variant.inventory']), (int) $request->quantity);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        abort_unless($request->user()->customer?->id === $item->cart?->customer_id, 403);

        $item->delete();

        return back()->with('status', 'Item removed from cart.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->cartService->clear($request->user()->customer);

        return back()->with('status', 'Cart cleared.');
    }
}
