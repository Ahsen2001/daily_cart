<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Carbon;

class CouponService
{
    public function findValid(?string $code, float $subtotal, ?int $vendorId = null): ?Coupon
    {
        if (! $code) {
            return null;
        }

        return Coupon::query()
            ->where('code', $code)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('vendor_id')->orWhere('vendor_id', $vendorId))
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now()))
            ->where('minimum_order_amount', '<=', $subtotal)
            ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->first();
    }

    public function discount(?Coupon $coupon, float $subtotal): float
    {
        if (! $coupon) {
            return 0.0;
        }

        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->max_discount_amount) {
            $discount = min($discount, (float) $coupon->max_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function markUsed(?Coupon $coupon): void
    {
        if ($coupon) {
            $coupon->increment('used_count');
        }
    }
}
