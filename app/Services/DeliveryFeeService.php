<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\DeliveryPricingRule;
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
    ): float {
        return $this->estimate($subtotal, $district, $distanceMeters, $customer)['delivery_fee'];
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
    ): array {
        $district = $this->resolveDistrict($district, $customer);
        $pricingRule = $this->matchingPricingRule($district, $province);

        if ($pricingRule) {
            return $this->estimateFromPricingRule($pricingRule, $subtotal, $distanceMeters);
        }

        $rule = $this->matchingRule($district);

        if (! $rule) {
            if (DeliveryPricingRule::query()->where('status', 'active')->exists()
                || DeliveryFee::query()->where('status', 'active')->exists()) {
                throw ValidationException::withMessages([
                    'delivery_district' => 'No active delivery fee configuration is available for the selected district.',
                ]);
            }

            return [
                'delivery_fee' => $this->financialPolicy->discountedDeliveryFee(OrderService::singleItemDeliveryCharge(), $subtotal),
                'estimated_delivery_minutes' => null,
                'free_delivery_eligible' => false,
                'rule_scope' => 'legacy_default',
                'rule_id' => null,
            ];
        }

        if ($subtotal < (float) $rule->minimum_order) {
            throw ValidationException::withMessages([
                'delivery_district' => 'The minimum order for '.$rule->district.' is '.CurrencyService::formatLkr($rule->minimum_order).'.',
            ]);
        }

        if ($rule->free_delivery_limit !== null && $subtotal >= (float) $rule->free_delivery_limit) {
            return [
                'delivery_fee' => 0.0,
                'estimated_delivery_minutes' => null,
                'free_delivery_eligible' => true,
                'rule_scope' => 'legacy_district',
                'rule_id' => null,
            ];
        }

        $distanceInKilometres = max(0, (int) $distanceMeters) / 1000;

        $fee = (float) $rule->base_fee + ((float) $rule->per_km_fee * $distanceInKilometres);

        return [
            'delivery_fee' => $this->financialPolicy->discountedDeliveryFee($fee, $subtotal),
            'estimated_delivery_minutes' => null,
            'free_delivery_eligible' => false,
            'rule_scope' => 'legacy_district',
            'rule_id' => null,
        ];
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
    private function estimateFromPricingRule(DeliveryPricingRule $rule, float $subtotal, ?int $distanceMeters): array
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
        $fee = $freeDelivery
            ? 0.0
            : $this->financialPolicy->discountedDeliveryFee(
                (float) $rule->base_fee + ((float) $rule->per_km_fee * $distanceInKilometres),
                $subtotal,
            );

        return [
            'delivery_fee' => round($fee, 2),
            'estimated_delivery_minutes' => $rule->estimated_delivery_minutes ?? $rule->zone?->estimated_delivery_minutes,
            'free_delivery_eligible' => $freeDelivery,
            'rule_scope' => $rule->scope,
            'rule_id' => $rule->id,
        ];
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
}
