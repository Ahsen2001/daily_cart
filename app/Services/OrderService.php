<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const SINGLE_ITEM_DELIVERY_CHARGE = 250.00;

    public const BULK_DELIVERY_CHARGE = 200.00;

    public const SERVICE_CHARGE_RATE = 0.02;

    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService,
        private readonly PaymentService $paymentService,
        private readonly NotificationService $notificationService,
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly ExternalEmailService $emails,
        private readonly DeliveryFeeService $deliveryFees,
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
            $pricingLines = $this->pricingLines(
                $cart,
                $data['coupon_code'] ?? null,
                $customer,
                (int) ($data['loyalty_points'] ?? 0),
                $data['delivery_district'] ?? null,
                isset($data['delivery_distance_meters']) ? (int) $data['delivery_distance_meters'] : null,
            );

            foreach ($pricingLines as $pricing) {
                $vendorId = $pricing['vendor_id'];
                $items = $pricing['items'];
                $subtotal = $pricing['subtotal'];
                $deliveryFee = $pricing['delivery_fee'];
                $coupon = $pricing['coupon'];
                $discount = $pricing['discount'];
                $serviceCharge = $pricing['service_charge'];
                $loyaltyPoints = $pricing['loyalty_points'];
                $loyaltyDiscount = $pricing['loyalty_discount'];
                $total = $pricing['total'];

                $placedAt = now();

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
                    'placed_at' => $placedAt,
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
                $this->notificationService->send(
                    $order->vendor->user,
                    'New order received',
                    'New order '.$order->order_number.' is waiting for action.',
                    'new_order',
                    ['database', 'mail', 'sms'],
                );
                $this->emails->orderPlaced($order->loadMissing('customer.user'));
                $order->delivery()->create([
                    'pickup_address' => $order->vendor->address,
                    'delivery_address' => $order->delivery_address,
                    'scheduled_at' => $order->scheduled_delivery_at,
                    'status' => 'pending',
                ]);

                $this->couponService->markUsed($coupon, $customer, $order, $discount);
                $this->loyaltyPointService->redeem($customer, $order, $loyaltyPoints);

                $orders[] = $order;
            }

            $cart->items()->delete();
            $cart->update(['status' => 'converted']);

            return $orders;
        });
    }

    public function quote(
        Cart $cart,
        ?string $couponCode = null,
        ?Customer $customer = null,
        int $loyaltyPoints = 0,
        ?string $deliveryDistrict = null,
        ?int $deliveryDistanceMeters = null,
    ): array {
        $lines = collect($this->pricingLines(
            $cart,
            $couponCode,
            $customer,
            $loyaltyPoints,
            $deliveryDistrict,
            $deliveryDistanceMeters,
        ));
        $subtotal = round((float) $lines->sum('subtotal'), 2);
        $deliveryEstimate = $this->deliveryFees->estimate(
            $subtotal,
            $deliveryDistrict,
            $deliveryDistanceMeters,
            $customer,
            null,
            $couponCode,
        );

        return [
            'coupon' => $lines->firstWhere('coupon', '!=', null)['coupon'] ?? null,
            'subtotal' => $subtotal,
            'discount' => round((float) $lines->sum('discount'), 2),
            'loyalty_points' => (int) $lines->sum('loyalty_points'),
            'loyalty_discount' => round((float) $lines->sum('loyalty_discount'), 2),
            'delivery_fee' => round((float) $lines->sum('delivery_fee'), 2),
            'service_charge' => round((float) $lines->sum('service_charge'), 2),
            'grand_total' => round((float) $lines->sum('total'), 2),
            'estimated_delivery_minutes' => $deliveryEstimate['estimated_delivery_minutes'],
            'free_delivery_eligible' => $deliveryEstimate['free_delivery_eligible'],
            'delivery_rule_scope' => $deliveryEstimate['rule_scope'],
        ];
    }

    /**
     * Calculate the per-vendor totals used by both checkout quotes and order creation.
     *
     * Delivery and service charges are calculated once for the whole checkout, then
     * apportioned to the vendor orders so their stored totals still reconcile.
     *
     * @return array<int, array<string, mixed>>
     */
    private function pricingLines(
        Cart $cart,
        ?string $couponCode,
        ?Customer $customer,
        int $loyaltyPoints,
        ?string $deliveryDistrict = null,
        ?int $deliveryDistanceMeters = null,
    ): array {
        $this->cartService->refreshPrices($cart);
        $cart->loadMissing(['items.product.category', 'items.variant.inventory']);

        $lines = [];
        $couponApplied = false;

        foreach ($cart->items->groupBy(fn (CartItem $item) => $item->product->vendor_id) as $vendorId => $items) {
            $subtotal = round((float) $items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity), 2);
            $coupon = $couponApplied
                ? null
                : $this->couponService->findValid($couponCode, $subtotal, (int) $vendorId, $customer);

            $lines[] = [
                'vendor_id' => (int) $vendorId,
                'items' => $items,
                'coupon' => $coupon,
                'subtotal' => $subtotal,
                'discount' => 0.0,
            ];

            $couponApplied = $coupon !== null || $couponApplied;
        }

        $checkoutSubtotal = round((float) collect($lines)->sum('subtotal'), 2);
        $checkoutDeliveryFee = $this->deliveryFees->calculate(
            $checkoutSubtotal,
            $deliveryDistrict,
            $deliveryDistanceMeters,
            1,
            $customer,
            $couponCode,
        );
        $checkoutServiceCharge = self::serviceChargeForSubtotal($checkoutSubtotal);

        foreach ($lines as &$line) {
            $line['discount'] = $this->couponService->discount(
                $line['coupon'],
                $line['subtotal'],
                $line['coupon'] ? $checkoutDeliveryFee : 0,
            );
        }
        unset($line);

        $this->allocateCheckoutCharge($lines, 'delivery_fee', $checkoutDeliveryFee);
        $this->allocateCheckoutCharge($lines, 'service_charge', $checkoutServiceCharge);

        foreach ($lines as &$line) {
            $line['before_loyalty_total'] = round(
                $line['subtotal'] - $line['discount'] + $line['delivery_fee'] + $line['service_charge'],
                2,
            );
        }
        unset($line);

        $beforeLoyaltyTotal = round((float) collect($lines)->sum('before_loyalty_total'), 2);
        $redemptionValuePerPoint = (float) $this->loyaltyPointService->setting()->redemption_value_per_point;

        if ($customer) {
            $this->loyaltyPointService->validateRedemption($customer, $loyaltyPoints, $beforeLoyaltyTotal);
        } elseif ($loyaltyPoints > 0) {
            throw ValidationException::withMessages([
                'loyalty_points' => 'A customer is required to redeem loyalty points.',
            ]);
        }

        $pointCapacity = $redemptionValuePerPoint > 0
            ? (int) collect($lines)->sum(fn (array $line) => floor($line['before_loyalty_total'] / $redemptionValuePerPoint))
            : 0;

        if ($loyaltyPoints > $pointCapacity) {
            throw ValidationException::withMessages([
                'loyalty_points' => 'Loyalty discount cannot make an order total negative.',
            ]);
        }

        $remainingPoints = $loyaltyPoints;

        foreach ($lines as &$line) {
            $maxPoints = $redemptionValuePerPoint > 0
                ? (int) floor($line['before_loyalty_total'] / $redemptionValuePerPoint)
                : 0;
            $line['loyalty_points'] = min($remainingPoints, $maxPoints);
            $line['loyalty_discount'] = round($line['loyalty_points'] * $redemptionValuePerPoint, 2);
            $line['total'] = round($line['before_loyalty_total'] - $line['loyalty_discount'], 2);
            $remainingPoints -= $line['loyalty_points'];
        }
        unset($line);

        return $lines;
    }

    /**
     * Apportion a checkout-wide charge across the orders created for each vendor.
     *
     * @param array<int, array<string, mixed>> $lines
     */
    private function allocateCheckoutCharge(array &$lines, string $field, float $amount): void
    {
        $amount = round($amount, 2);
        $checkoutSubtotal = round((float) collect($lines)->sum('subtotal'), 2);
        $remaining = $amount;
        $lastIndex = array_key_last($lines);

        foreach ($lines as $index => &$line) {
            $allocation = $index === $lastIndex
                ? $remaining
                : ($checkoutSubtotal > 0
                    ? round($amount * ((float) $line['subtotal'] / $checkoutSubtotal), 2)
                    : 0.0);

            $line[$field] = $allocation;
            $remaining = round($remaining - $allocation, 2);
        }
        unset($line);
    }

    public static function deliveryChargeForQuantity(int $quantity): float
    {
        if ($quantity <= 0) {
            return 0.0;
        }

        return $quantity === 1
            ? self::singleItemDeliveryCharge()
            : self::bulkDeliveryCharge();
    }

    public static function singleItemDeliveryCharge(): float
    {
        return self::settingFloat('delivery_charge_single_item', self::SINGLE_ITEM_DELIVERY_CHARGE);
    }

    public static function bulkDeliveryCharge(): float
    {
        return self::settingFloat('delivery_charge_bulk_items', self::BULK_DELIVERY_CHARGE);
    }

    public static function serviceChargeRate(): float
    {
        return self::settingFloat('service_charge_rate_percent', self::SERVICE_CHARGE_RATE * 100) / 100;
    }

    public static function serviceChargeForSubtotal(float|int|string $subtotal, ?float $rate = null): float
    {
        if ($rate !== null) {
            return round((float) $subtotal * $rate, 2);
        }

        return app(FinancialPolicyService::class)->serviceCharge($subtotal);
    }

    private static function settingFloat(string $key, float $default): float
    {
        $value = Setting::query()->where('setting_key', $key)->value('setting_value');

        return is_numeric($value) ? max(0, (float) $value) : $default;
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
