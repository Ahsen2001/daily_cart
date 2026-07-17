<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(private readonly PromotionService $promotions) {}

    public function activeCart(Customer $customer): Cart
    {
        return Cart::firstOrCreate(
            ['customer_id' => $customer->id, 'status' => 'active'],
            ['status' => 'active']
        );
    }

    public function add(Customer $customer, Product $product, int $quantity, ?ProductVariant $variant = null): CartItem
    {
        $this->ensureProductCanBeOrdered($product, $quantity, $variant);

        $cart = $this->activeCart($customer);

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        $newQuantity = $quantity + ($item?->quantity ?? 0);
        $this->ensureProductCanBeOrdered($product, $newQuantity, $variant);

        return $cart->items()->updateOrCreate(
            [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
            ],
            [
                'quantity' => $newQuantity,
                'unit_price' => $this->unitPrice($product, $variant),
            ]
        );
    }

    public function update(CartItem $item, int $quantity): CartItem
    {
        $this->ensureProductCanBeOrdered($item->product, $quantity, $item->variant);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $this->unitPrice($item->product, $item->variant),
        ]);

        return $item;
    }

    public function clear(Customer $customer): void
    {
        $this->activeCart($customer)->items()->delete();
    }

    public function totals(Cart $cart): array
    {
        $this->refreshPrices($cart);
        $subtotal = $cart->items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);

        return [
            'subtotal' => round($subtotal, 2),
            'item_count' => $cart->items->sum('quantity'),
        ];
    }

    public function refreshPrices(Cart $cart): Cart
    {
        $cart->loadMissing(['items.product.category', 'items.product.vendor', 'items.variant']);

        foreach ($cart->items as $item) {
            $unitPrice = $this->unitPrice($item->product, $item->variant);

            if (round((float) $item->unit_price, 2) !== $unitPrice) {
                $item->update(['unit_price' => $unitPrice]);
            }
        }

        return $cart;
    }

    public function unitPrice(Product $product, ?ProductVariant $variant = null): float
    {
        return $this->promotions->pricingFor($product, $variant)['final_price'];
    }

    public function ensureProductCanBeOrdered(Product $product, int $quantity, ?ProductVariant $variant = null): void
    {
        if ($variant && (int) $variant->product_id !== (int) $product->id) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'The selected variant does not belong to this product.',
            ]);
        }

        if ($product->status !== 'approved'
            || $product->category?->status !== 'active'
            || $product->vendor?->status !== 'approved') {
            throw ValidationException::withMessages([
                'product' => 'This product is not available for ordering.',
            ]);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        if ($this->availableStock($product, $variant) < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Requested quantity is not available in stock.',
            ]);
        }
    }

    public function availableStock(Product $product, ?ProductVariant $variant = null): int
    {
        if ($variant) {
            $variantStock = $variant->inventory?->quantity;

            if ($variantStock !== null) {
                return min((int) $variantStock, (int) $product->stock_quantity);
            }
        }

        return (int) $product->stock_quantity;
    }
}
