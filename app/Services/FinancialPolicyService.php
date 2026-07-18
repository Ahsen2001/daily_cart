<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\RiderPaymentRule;
use App\Models\Setting;
use App\Models\Vendor;

class FinancialPolicyService
{
    /** @return array<string, float> */
    public function settings(): array
    {
        $defaults = [
            'service_charge_rate_percent' => 2.0,
            'service_charge_flat_amount' => 0.0,
            'service_charge_minimum' => 0.0,
            'service_charge_maximum' => 0.0,
            'delivery_promotion_discount_percent' => 0.0,
            'delivery_promotion_minimum_subtotal' => 0.0,
            'rider_payout_base' => 350.0,
            'rider_payout_per_km' => 0.0,
            'rider_peak_bonus' => 0.0,
            'rider_peak_start_hour' => 17.0,
            'rider_peak_end_hour' => 21.0,
            'default_vendor_commission_rate' => 10.0,
        ];

        return collect(Setting::values($defaults))
            ->map(fn ($value) => max(0, is_numeric($value) ? (float) $value : 0.0))
            ->all();
    }

    public function serviceCharge(float|int|string $subtotal): float
    {
        $settings = $this->settings();
        $charge = ((float) $subtotal * ($settings['service_charge_rate_percent'] / 100))
            + $settings['service_charge_flat_amount'];

        if ($charge > 0 && $settings['service_charge_minimum'] > 0) {
            $charge = max($charge, $settings['service_charge_minimum']);
        }

        if ($settings['service_charge_maximum'] > 0) {
            $charge = min($charge, $settings['service_charge_maximum']);
        }

        return round($charge, 2);
    }

    public function riderPayout(Delivery $delivery): float
    {
        $distanceInKilometres = max(0, (int) ($delivery->order?->delivery_distance_meters ?? 0)) / 1000;
        return $this->riderPayoutForDistance($distanceInKilometres, $delivery->delivered_at ?? now());
    }

    public function riderPayoutForDistance(float $distanceInKilometres, \DateTimeInterface $at): float
    {
        $rule = $this->activeRiderRule();
        $settings = $this->settings();
        $payout = $rule ? (float) $rule->base_pay : $settings['rider_payout_base'];
        $payout += $distanceInKilometres * ($rule ? (float) $rule->per_km_bonus : $settings['rider_payout_per_km']);
        $hour = (int) $at->format('G');

        if ($rule && $this->isHourInRange($hour, $rule->peak_start_hour, $rule->peak_end_hour)) {
            $payout += (float) $rule->peak_hour_bonus;
        } elseif (! $rule && $this->isPeakHour($hour, $settings)) {
            $payout += $settings['rider_peak_bonus'];
        }

        if ($rule && $this->isHourInRange($hour, $rule->night_start_hour, $rule->night_end_hour)) {
            $payout += (float) $rule->night_bonus;
        }

        return round($payout, 2);
    }

    private function activeRiderRule(): ?RiderPaymentRule
    {
        return RiderPaymentRule::query()->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', today()))
            ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', today()))
            ->orderBy('priority')->orderBy('id')->first();
    }

    private function isHourInRange(int $hour, ?int $start, ?int $end): bool
    {
        if ($start === null || $end === null) {
            return false;
        }

        return $start <= $end ? $hour >= $start && $hour < $end : $hour >= $start || $hour < $end;
    }

    public function discountedDeliveryFee(float $fee, float $subtotal): float
    {
        $settings = $this->settings();

        if ($settings['delivery_promotion_discount_percent'] <= 0
            || $subtotal < $settings['delivery_promotion_minimum_subtotal']) {
            return round($fee, 2);
        }

        return round($fee * (1 - min(100, $settings['delivery_promotion_discount_percent']) / 100), 2);
    }

    public function vendorCommissionRate(Vendor $vendor): float
    {
        $rate = (float) $vendor->commission_rate;

        return $rate > 0 ? $rate : $this->settings()['default_vendor_commission_rate'];
    }

    public function vendorPayout(Order $order): float
    {
        $base = max(0, (float) $order->subtotal - (float) $order->discount_amount - (float) $order->loyalty_discount_amount);

        return $order->vendor ? $this->vendorPayoutForBase($order->vendor, $base) : $base;
    }

    public function vendorPayoutForBase(Vendor $vendor, float $base): float
    {
        $base = max(0, $base);
        $rate = $this->vendorCommissionRate($vendor);

        return round($base - ($base * ($rate / 100)), 2);
    }

    /** @param array<string, float> $settings */
    private function isPeakHour(int $hour, array $settings): bool
    {
        $start = (int) $settings['rider_peak_start_hour'];
        $end = (int) $settings['rider_peak_end_hour'];

        return $start <= $end
            ? $hour >= $start && $hour < $end
            : $hour >= $start || $hour < $end;
    }
}
