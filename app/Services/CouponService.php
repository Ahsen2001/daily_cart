<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function findValid(?string $code, float $subtotal, ?int $vendorId = null, ?Customer $customer = null): ?Coupon
    {
        if (! $code) {
            return null;
        }

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('vendor_id')->orWhere('vendor_id', $vendorId))
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now()))
            ->where('minimum_order_amount', '<=', $subtotal)
            ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->first();

        if ($coupon && $customer && $coupon->per_customer_limit) {
            $usedByCustomer = $coupon->redemptions()->where('customer_id', $customer->id)->count();

            if ($usedByCustomer >= $coupon->per_customer_limit) {
                return null;
            }
        }

        return $coupon;
    }

    public function discount(?Coupon $coupon, float $subtotal, float $deliveryFee = 0): float
    {
        if (! $coupon) {
            return 0.0;
        }

        $type = $coupon->discount_type ?? ($coupon->type === 'percentage' ? 'percentage' : 'fixed_amount');
        $value = (float) ($coupon->discount_value ?: $coupon->value);

        $discount = match ($type) {
            'percentage' => $subtotal * ($value / 100),
            'free_delivery' => $deliveryFee,
            default => $value,
        };

        $maximum = $coupon->maximum_discount_amount ?? $coupon->max_discount_amount;

        if ($maximum) {
            $discount = min($discount, (float) $maximum);
        }

        return round(min($discount, $subtotal + $deliveryFee), 2);
    }

    public function markUsed(?Coupon $coupon, ?Customer $customer = null, ?Order $order = null, float $discount = 0): void
    {
        if ($coupon) {
            $coupon->increment('used_count');

            if ($customer && $order) {
                $coupon->redemptions()->firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'customer_id' => $customer->id,
                        'discount_amount' => $discount,
                    ]
                );
            }
        }
    }

    public function validateOrFail(?Coupon $coupon): void
    {
        if (! $coupon) {
            throw ValidationException::withMessages(['coupon_code' => 'Coupon is invalid, expired, or not available for this order.']);
        }
    }
}
