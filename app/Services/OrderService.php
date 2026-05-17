<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
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
        private readonly NotificationService $notificationService,
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly ExternalEmailService $emails,
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
            $couponApplied = false;
            $remainingLoyaltyPoints = (int) ($data['loyalty_points'] ?? 0);
            $redemptionValuePerPoint = (float) $this->loyaltyPointService->setting()->redemption_value_per_point;

            foreach ($cart->items->groupBy(fn (CartItem $item) => $item->product->vendor_id) as $vendorId => $items) {
                $subtotal = $items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);
                $deliveryFee = self::DELIVERY_CHARGE;
                $coupon = $couponApplied ? null : $this->couponService->findValid($data['coupon_code'] ?? null, $subtotal, (int) $vendorId, $customer);
                $discount = $this->couponService->discount($coupon, $subtotal, $deliveryFee);
                $serviceCharge = round($subtotal * self::SERVICE_CHARGE_RATE, 2);
                $beforeLoyaltyTotal = round($subtotal - $discount + $deliveryFee + $serviceCharge, 2);
                $maxPointsForThisOrder = $redemptionValuePerPoint > 0 ? (int) floor($beforeLoyaltyTotal / $redemptionValuePerPoint) : 0;
                $loyaltyPoints = min($remainingLoyaltyPoints, $maxPointsForThisOrder);
                $loyaltyDiscount = $this->loyaltyPointService->validateRedemption($customer, $loyaltyPoints, $beforeLoyaltyTotal);
                $total = round($beforeLoyaltyTotal - $loyaltyDiscount, 2);

                $order = Order::create([
                    'order_number' => $this->orderNumber(),
                    'customer_id' => $customer->id,
                    'vendor_id' => (int) $vendorId,
                    'coupon_id' => $coupon?->id,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'loyalty_points_redeemed' => $loyaltyPoints,
                    'loyalty_discount_amount' => $loyaltyDiscount,
                    'delivery_fee' => $deliveryFee,
                    'service_charge' => $serviceCharge,
                    'tax_amount' => 0,
                    'total_amount' => $total,
                    'currency' => CurrencyService::CURRENCY,
                    'delivery_address' => $data['delivery_address'],
                    'delivery_latitude' => $data['delivery_latitude'] ?? null,
                    'delivery_longitude' => $data['delivery_longitude'] ?? null,
                    'delivery_distance_meters' => $data['delivery_distance_meters'] ?? null,
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
                $this->notificationService->send($order->customer->user, 'Order placed', 'Order '.$order->order_number.' has been placed.', 'order_placed');
                $this->notificationService->send($order->vendor->user, 'New order received', 'New order '.$order->order_number.' is waiting for action.', 'new_order');
                $this->emails->orderPlaced($order->loadMissing('customer.user'));
                $order->delivery()->create([
                    'pickup_address' => $order->vendor->address,
                    'delivery_address' => $order->delivery_address,
                    'scheduled_at' => $order->scheduled_delivery_at,
                    'status' => 'pending',
                ]);

                $this->couponService->markUsed($coupon, $customer, $order, $discount);
                $this->loyaltyPointService->redeem($customer, $order, $loyaltyPoints);
                $couponApplied = $coupon !== null || $couponApplied;
                $remainingLoyaltyPoints -= $loyaltyPoints;

                $orders[] = $order;
            }

            $cart->items()->delete();
            $cart->update(['status' => 'converted']);

            return $orders;
        });
    }

    public function quote(Cart $cart, ?string $couponCode = null, ?Customer $customer = null, int $loyaltyPoints = 0): array
    {
        $cart->loadMissing(['items.product.category', 'items.variant']);

        $subtotal = $cart->items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);
        $deliveryFee = $cart->items->isEmpty() ? 0 : self::DELIVERY_CHARGE;
        $coupon = null;
        $discount = 0.0;

        foreach ($cart->items->groupBy(fn (CartItem $item) => $item->product->vendor_id) as $vendorId => $items) {
            $vendorSubtotal = $items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);
            $coupon = $this->couponService->findValid($couponCode, $vendorSubtotal, (int) $vendorId, $customer);

            if ($coupon) {
                $discount = $this->couponService->discount($coupon, $vendorSubtotal, $deliveryFee);
                break;
            }
        }

        $serviceCharge = round($subtotal * self::SERVICE_CHARGE_RATE, 2);
        $beforeLoyaltyTotal = round($subtotal - $discount + $deliveryFee + $serviceCharge, 2);
        $loyaltyDiscount = $customer ? $this->loyaltyPointService->validateRedemption($customer, $loyaltyPoints, $beforeLoyaltyTotal) : 0;

        return [
            'coupon' => $coupon,
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'loyalty_points' => $loyaltyPoints,
            'loyalty_discount' => $loyaltyDiscount,
            'delivery_fee' => $deliveryFee,
            'service_charge' => $serviceCharge,
            'grand_total' => round($beforeLoyaltyTotal - $loyaltyDiscount, 2),
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

        $this->notificationService->lowStockAlert($product->refresh());
    }

    private function orderNumber(): string
    {
        do {
            $number = 'DC-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
