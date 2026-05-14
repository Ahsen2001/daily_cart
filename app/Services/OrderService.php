<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const DELIVERY_CHARGE = 350.00;
    public const SERVICE_CHARGE_RATE = 0.02;

    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * @return array<int, Order>
     */
    public function createFromCart(Customer $customer, array $data): array
    {
        $cart = $this->cartService->activeCart($customer)->load(['items.product.category', 'items.variant.inventory']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        return DB::transaction(function () use ($cart, $customer, $data) {
            $orders = [];

            foreach ($cart->items->groupBy(fn (CartItem $item) => $item->product->vendor_id) as $vendorId => $items) {
                $subtotal = $items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);
                $coupon = $this->couponService->findValid($data['coupon_code'] ?? null, $subtotal, (int) $vendorId);
                $discount = $this->couponService->discount($coupon, $subtotal);
                $deliveryFee = self::DELIVERY_CHARGE;
                $serviceCharge = round($subtotal * self::SERVICE_CHARGE_RATE, 2);
                $total = round($subtotal - $discount + $deliveryFee + $serviceCharge, 2);

                $order = Order::create([
                    'order_number' => $this->orderNumber(),
                    'customer_id' => $customer->id,
                    'vendor_id' => (int) $vendorId,
                    'coupon_id' => $coupon?->id,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'delivery_fee' => $deliveryFee,
                    'service_charge' => $serviceCharge,
                    'tax_amount' => 0,
                    'total_amount' => $total,
                    'currency' => CurrencyService::CURRENCY,
                    'delivery_address' => $data['delivery_address'],
                    'order_status' => 'pending',
                    'payment_status' => 'pending',
                    'placed_at' => Carbon::now(),
                    'scheduled_delivery_at' => Carbon::parse($data['scheduled_delivery_at']),
                ]);

                foreach ($items as $item) {
                    $this->reduceStock($item->product_id, $item->product_variant_id, $item->quantity);

                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'vendor_id' => $item->product->vendor_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => round((float) $item->unit_price * $item->quantity, 2),
                    ]);
                }

                $this->paymentService->createPlaceholder($order, $data['payment_method']);
                $this->couponService->markUsed($coupon);

                $orders[] = $order;
            }

            $cart->items()->delete();
            $cart->update(['status' => 'converted']);

            return $orders;
        });
    }

    public function quote(Cart $cart, ?string $couponCode = null): array
    {
        $cart->loadMissing(['items.product.category', 'items.variant']);

        $subtotal = $cart->items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);
        $coupon = $this->couponService->findValid($couponCode, $subtotal);
        $discount = $this->couponService->discount($coupon, $subtotal);
        $deliveryFee = $cart->items->isEmpty() ? 0 : self::DELIVERY_CHARGE;
        $serviceCharge = round($subtotal * self::SERVICE_CHARGE_RATE, 2);

        return [
            'coupon' => $coupon,
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'delivery_fee' => $deliveryFee,
            'service_charge' => $serviceCharge,
            'grand_total' => round($subtotal - $discount + $deliveryFee + $serviceCharge, 2),
        ];
    }

    private function reduceStock(int $productId, ?int $variantId, int $quantity): void
    {
        $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
        $variant = $variantId ? ProductVariant::whereKey($variantId)->lockForUpdate()->first() : null;

        $this->cartService->ensureProductCanBeOrdered($product->load('category'), $quantity, $variant);

        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'stock' => 'A product in your cart is no longer available in the requested quantity.',
            ]);
        }

        $product->decrement('stock_quantity', $quantity);
        $product->refresh();

        if ($variant?->inventory) {
            $inventory = $variant->inventory()->lockForUpdate()->first();

            if ($inventory && $inventory->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'stock' => 'A product variant in your cart is no longer available in the requested quantity.',
                ]);
            }

            $inventory?->decrement('quantity', $quantity);
        }

        $product->inventory()->whereNull('product_variant_id')->decrement('quantity', $quantity);

        if ($product->stock_quantity <= 0) {
            $product->update(['status' => 'out_of_stock']);
        }
    }

    private function orderNumber(): string
    {
        do {
            $number = 'DC-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
