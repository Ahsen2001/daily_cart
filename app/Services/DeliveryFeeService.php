<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DeliveryFee;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DeliveryFeeService
{
    /**
     * Calculate one vendor delivery charge from the active admin configuration.
     */
    public function calculate(
        float $subtotal,
        ?string $district = null,
        ?int $distanceMeters = null,
        int $quantity = 1,
        ?Customer $customer = null,
    ): float {
        $district = $this->resolveDistrict($district, $customer);
        $rule = $this->matchingRule($district);

        if (! $rule) {
            if (DeliveryFee::query()->where('status', 'active')->exists()) {
                throw ValidationException::withMessages([
                    'delivery_district' => 'No active delivery fee configuration is available for the selected district.',
                ]);
            }

            return OrderService::deliveryChargeForQuantity($quantity);
        }

        if ($subtotal < (float) $rule->minimum_order) {
            throw ValidationException::withMessages([
                'delivery_district' => 'The minimum order for '.$rule->district.' is '.CurrencyService::formatLkr($rule->minimum_order).'.',
            ]);
        }

        if ($rule->free_delivery_limit !== null && $subtotal >= (float) $rule->free_delivery_limit) {
            return 0.0;
        }

        $distanceInKilometres = max(0, (int) $distanceMeters) / 1000;

        return round((float) $rule->base_fee + ((float) $rule->per_km_fee * $distanceInKilometres), 2);
    }

    /** @return Collection<int, string> */
    public function configuredDistricts(): Collection
    {
        return DeliveryFee::query()
            ->where('status', 'active')
            ->whereNotIn('district', ['All Districts', 'Default', '*'])
            ->orderBy('district')
            ->pluck('district');
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
