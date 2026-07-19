<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\DeliveryHoliday;
use App\Models\DeliveryPricingRule;
use App\Models\DeliveryPromotion;
use App\Models\FreeDeliveryRule;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DeliveryFeeService
{
    public function __construct(private readonly FinancialPolicyService $financialPolicy) {}

    /**
     * Calculate the one delivery charge for an entire checkout from the active admin configuration.
     */
    public function calculate(
        float $subtotal,
        ?string $district = null,
        ?int $distanceMeters = null,
        int $quantity = 1,
        ?Customer $customer = null,
        ?string $couponCode = null,
        ?string $province = null,
    ): float {
        return $this->estimate($subtotal, $district, $distanceMeters, $customer, $province, $couponCode)['delivery_fee'];
    }

    /**
     * Return the customer-facing checkout delivery estimate and its matching rule metadata.
     *
     * @return array{delivery_fee: float, estimated_delivery_minutes: int|null, free_delivery_eligible: bool, rule_scope: string, rule_id: int|null}
     */
    public function estimate(
        float $subtotal,
        ?string $district = null,
        ?int $distanceMeters = null,
        ?Customer $customer = null,
        ?string $province = null,
        ?string $couponCode = null,
    ): array {
        $district = $this->resolveDistrict($district, $customer);
        $province = $this->resolveProvince($province, $customer);
        $pricingRule = $this->matchingPricingRule($district, $province);

        if ($pricingRule) {
            return $this->estimateFromPricingRule($pricingRule, $subtotal, $distanceMeters, $customer, $couponCode);
        }

        $rule = $this->matchingRule($district);

        if (! $rule) {
            if (DeliveryPricingRule::query()->where('status', 'active')->exists()
                || DeliveryFee::query()->where('status', 'active')->exists()) {
                throw ValidationException::withMessages([
                    'delivery_district' => 'No active delivery fee configuration is available for the selected district.',
                ]);
            }

            return $this->finalizeEstimate(OrderService::singleItemDeliveryCharge(), $subtotal, null, 'legacy_default', null, $customer, $couponCode);
        }

        if ($subtotal < (float) $rule->minimum_order) {
            throw ValidationException::withMessages([
                'delivery_district' => 'The minimum order for '.$rule->district.' is '.CurrencyService::formatLkr($rule->minimum_order).'.',
            ]);
        }

        if ($rule->free_delivery_limit !== null && $subtotal >= (float) $rule->free_delivery_limit) {
            return $this->finalizeEstimate(0, $subtotal, null, 'legacy_district', null, $customer, $couponCode, true);
        }

        $distanceInKilometres = max(0, (int) $distanceMeters) / 1000;

        $fee = (float) $rule->base_fee + ((float) $rule->per_km_fee * $distanceInKilometres);

        return $this->finalizeEstimate($fee, $subtotal, null, 'legacy_district', null, $customer, $couponCode);
    }

    /** @return Collection<int, string> */
    public function configuredDistricts(): Collection
    {
        return $this->configuredRules()->pluck('district');
    }

    /** @return Collection<int, DeliveryFee> */
    public function configuredRules(): Collection
    {
        return DeliveryFee::query()
            ->where('status', 'active')
            ->whereNotIn('district', ['All Districts', 'Default', '*'])
            ->orderBy('district')
            ->get();
    }

    private function matchingRule(?string $district): ?DeliveryFee
    {
        $query = DeliveryFee::query()->where('status', 'active');

        if ($district) {
            $rule = (clone $query)
                ->whereRaw('LOWER(district) = ?', [mb_strtolower($district)])
                ->first();

            if ($rule) {
                return $rule;
            }
        }

        return $query
            ->where(function ($fallback) {
                $fallback->whereRaw('LOWER(district) = ?', ['all districts'])
                    ->orWhereRaw('LOWER(district) = ?', ['default'])
                    ->orWhere('district', '*');
            })
            ->orderByRaw("CASE LOWER(district) WHEN 'all districts' THEN 1 WHEN 'default' THEN 2 ELSE 3 END")
            ->first();
    }

    private function matchingPricingRule(?string $district, ?string $province): ?DeliveryPricingRule
    {
        $today = now()->toDateString();
        $rules = DeliveryPricingRule::query()
            ->with('zone')
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', $today))
            ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $today))
            ->get();

        return $rules
            ->filter(function (DeliveryPricingRule $rule) use ($district, $province) {
                return match ($rule->scope) {
                    'zone' => $district && $rule->zone && strcasecmp((string) $rule->zone->district, $district) === 0,
                    'district' => $district && strcasecmp((string) $rule->district, $district) === 0,
                    'province' => $province && strcasecmp((string) $rule->province, $province) === 0,
                    'default' => true,
                    default => false,
                };
            })
            ->sortBy(fn (DeliveryPricingRule $rule) => [$this->scopeRank($rule->scope), $rule->priority, $rule->id])
            ->first();
    }

    /** @return array{delivery_fee: float, estimated_delivery_minutes: int|null, free_delivery_eligible: bool, rule_scope: string, rule_id: int|null} */
    private function estimateFromPricingRule(DeliveryPricingRule $rule, float $subtotal, ?int $distanceMeters, ?Customer $customer, ?string $couponCode): array
    {
        if ($subtotal < (float) $rule->minimum_order) {
            throw ValidationException::withMessages([
                'delivery_district' => 'The minimum order for this delivery rule is '.CurrencyService::formatLkr($rule->minimum_order).'.',
            ]);
        }

        $distanceInKilometres = max(0, (int) $distanceMeters) / 1000;

        if ($rule->maximum_distance_km !== null && $distanceInKilometres > (float) $rule->maximum_distance_km) {
            throw ValidationException::withMessages([
                'delivery_distance_meters' => 'The delivery location is outside the maximum distance for this delivery rule.',
            ]);
        }

        $freeDelivery = $rule->free_delivery_threshold !== null
            && $subtotal >= (float) $rule->free_delivery_threshold;

        return $this->finalizeEstimate(
            (float) $rule->base_fee + ((float) $rule->per_km_fee * $distanceInKilometres),
            $subtotal,
            $rule->estimated_delivery_minutes ?? $rule->zone?->estimated_delivery_minutes,
            $rule->scope,
            $rule->id,
            $customer,
            $couponCode,
            $freeDelivery,
        );
    }

    /** @return array{delivery_fee: float, estimated_delivery_minutes: int|null, free_delivery_eligible: bool, rule_scope: string, rule_id: int|null} */
    private function finalizeEstimate(float $baseFee, float $subtotal, ?int $estimatedMinutes, string $scope, ?int $ruleId, ?Customer $customer, ?string $couponCode, bool $ruleFree = false): array
    {
        $freeRule = $this->matchingFreeDeliveryRule($subtotal, $customer, $couponCode);
        $promotion = $this->matchingPromotion($subtotal);
        $free = $ruleFree || $freeRule !== null || in_array($promotion?->type, ['free_delivery', 'vendor_sponsored'], true);
        $fee = $free ? 0.0 : $baseFee;

        if (! $free && $promotion?->type === 'percentage_discount') {
            $fee *= 1 - min(100, (float) $promotion->discount_percent) / 100;
        } elseif (! $free) {
            $fee = $this->financialPolicy->discountedDeliveryFee($fee, $subtotal);
        }

        if (! $free) {
            $fee += (float) (DeliveryHoliday::query()->where('status', 'active')
                ->whereDate('starts_on', '<=', today())->whereDate('ends_on', '>=', today())
                ->sum('extra_charge'));
        }

        return [
            'delivery_fee' => round($fee, 2),
            'estimated_delivery_minutes' => $estimatedMinutes,
            'free_delivery_eligible' => $free,
            'rule_scope' => $scope,
            'rule_id' => $ruleId,
        ];
    }

    private function matchingPromotion(float $subtotal): ?DeliveryPromotion
    {
        return DeliveryPromotion::query()->where('status', 'active')
            ->where('minimum_order', '<=', $subtotal)
            ->where(fn ($query) => $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', today()))
            ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', today()))
            ->orderBy('priority')->orderBy('id')->first();
    }

    private function matchingFreeDeliveryRule(float $subtotal, ?Customer $customer, ?string $couponCode): ?FreeDeliveryRule
    {
        return FreeDeliveryRule::query()->where('status', 'active')
            ->where('minimum_order', '<=', $subtotal)
            ->where(fn ($query) => $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', today()))
            ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', today()))
            ->orderBy('priority')->orderBy('id')->get()
            ->first(function (FreeDeliveryRule $rule) use ($customer, $couponCode) {
                return match ($rule->condition_type) {
                    'subtotal' => true,
                    'first_order' => $customer && ! $customer->orders()->exists(),
                    'weekend' => now()->isWeekend(),
                    'coupon' => $couponCode && strcasecmp(trim($rule->coupon_code ?? ''), trim($couponCode)) === 0,
                    'premium_membership' => $customer && $customer->subscriptions()->where('status', 'active')->exists(),
                    default => false,
                };
            });
    }

    private function scopeRank(string $scope): int
    {
        return match ($scope) {
            'zone' => 1,
            'district' => 2,
            'province' => 3,
            default => 4,
        };
    }

    private function resolveDistrict(?string $district, ?Customer $customer): ?string
    {
        $district = trim((string) $district);

        if ($district !== '') {
            return $district;
        }

        $savedDistrict = $customer?->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->value('district');

        $savedDistrict = trim((string) $savedDistrict);

        return $savedDistrict !== '' ? $savedDistrict : null;
    }

    private function resolveProvince(?string $province, ?Customer $customer): ?string
    {
        $province = trim((string) $province);

        if ($province !== '') {
            return $province;
        }

        $savedProvince = $customer?->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->value('province');

        $savedProvince = trim((string) $savedProvince);

        return $savedProvince !== '' ? $savedProvince : null;
    }
}
