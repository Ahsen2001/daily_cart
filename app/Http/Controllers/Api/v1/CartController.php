<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function show(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $cart = $this->cartService->activeCart($customer);
        $cart->load('items.product', 'items.variant');

        return response()->json([
            'cart' => $cart->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->unit_price * $item->quantity,
                'variant_id' => $item->product_variant_id,
                'variant_name' => $item->variant?->name,
            ]),
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
        ]);

        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $product = Product::findOrFail($request->product_id);
        $variant = $request->product_variant_id ? ProductVariant::findOrFail($request->product_variant_id) : null;

        $item = $this->cartService->add($customer, $product, $request->quantity, $variant);

        return response()->json([
            'message' => 'Product added to cart successfully.',
            'item' => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
            ],
        ]);
    }

    public function update(Request $request, CartItem $item): JsonResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $customer = $request->user()->customer;
        if (! $customer || $item->cart->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $this->cartService->update($item, $request->quantity);

        return response()->json([
            'message' => 'Cart item quantity updated.',
        ]);
    }

    public function remove(Request $request, CartItem $item): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer || $item->cart->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed from cart.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['message' => 'Customer profile not found.'], 404);
        }

        $this->cartService->clear($customer);

        return response()->json([
            'message' => 'Cart cleared successfully.',
        ]);
    }
}
