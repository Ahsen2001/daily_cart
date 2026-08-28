<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryFee;
use App\Models\DeliveryHoliday;
use App\Models\DeliveryPricingRule;
use App\Models\DeliveryPromotion;
use App\Models\DeliveryRuleHistory;
use App\Models\FreeDeliveryRule;
use App\Models\RiderPaymentRule;
use App\Models\Setting;
use App\Models\Zone;
use App\Services\DeliveryFeeService;
use App\Services\FinancialPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DeliveryEngineController extends Controller
{
    public function zones(): View
    {
        return view('admin.delivery-engine.zones', ['zones' => Zone::query()->latest()->paginate(20)]);
    }

    public function storeZone(Request $request): RedirectResponse
    {
        Zone::query()->create($this->zoneData($request));

        return back()->with('status', 'Delivery zone created.');
    }

    public function updateZone(Request $request, Zone $zone): RedirectResponse
    {
        $zone->update($this->zoneData($request));

        return back()->with('status', 'Delivery zone updated.');
    }

    public function destroyZone(Zone $zone): RedirectResponse
    {
        $zone->delete();

        return back()->with('status', 'Delivery zone deleted. Existing orders are unchanged.');
    }

    public function rules(): View
    {
        return $this->rulesView('super-admin.delivery.rules.store');
    }

    public function adminRules(): View
    {
        return $this->rulesView('admin.delivery.rules.store');
    }

    private function rulesView(string $storeRoute): View
    {
        return view('admin.delivery-engine.rules', [
            'rules' => DeliveryPricingRule::query()->with('zone')->orderBy('priority')->paginate(20),
            'zones' => Zone::query()->where('status', 'active')->orderBy('name')->get(),
            'storeRoute' => $storeRoute,
            'updateRoute' => str_replace('.store', '.update', $storeRoute),
            'destroyRoute' => str_replace('.store', '.destroy', $storeRoute),
        ]);
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $rule = DeliveryPricingRule::query()->create($this->ruleData($request));
        $this->recordRuleHistory($request, $rule, 'created', $rule->getAttributes());

        return back()->with('status', 'Delivery pricing rule created.');
    }

    public function updateRule(Request $request, DeliveryPricingRule $rule): RedirectResponse
    {
        $rule->update($this->ruleData($request));
        $this->recordRuleHistory($request, $rule, 'updated', $rule->getChanges());

        return back()->with('status', 'Delivery pricing rule updated.');
    }

    public function destroyRule(Request $request, DeliveryPricingRule $rule): RedirectResponse
    {
        $this->recordRuleHistory($request, $rule, 'deleted', $rule->getAttributes());
        $rule->delete();

        return back()->with('status', 'Delivery pricing rule deleted. Existing orders are unchanged.');
    }

    public function simulator(Request $request, DeliveryFeeService $deliveryFees, FinancialPolicyService $financialPolicy): View
    {
        $data = $request->validate([
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'zone_id' => [
                'nullable',
                'integer',
                Rule::exists('zones', 'id')->where('status', 'active'),
            ],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string', 'max:1000'],
            'distance_meters' => ['nullable', 'integer', 'min:0'],
        ]);

        $zones = Zone::query()
            ->where('status', 'active')
            ->orderBy('province')
            ->orderBy('district')
            ->orderBy('name')
            ->get();
        $selectedZone = isset($data['zone_id'])
            ? $zones->firstWhere('id', (int) $data['zone_id'])
            : null;
        $fullAddress = trim((string) ($data['full_address'] ?? ''));
        $locationSource = $selectedZone ? 'selected zone' : null;
        $matchedLocation = $selectedZone?->name;
        $detectedDistrict = null;
        $detectedProvince = null;

        if (! $selectedZone && $fullAddress !== '') {
            $detected = $this->detectSimulatorLocation($fullAddress, $zones);
            $selectedZone = $detected['zone'];
            $detectedDistrict = $detected['district'];
            $detectedProvince = $detected['province'];
            $locationSource = $detected['source'];
            $matchedLocation = $detected['matched_location'];
        }

        $district = $selectedZone?->district
            ?: $detectedDistrict
            ?: ($data['district'] ?? null);
        $province = $selectedZone?->province
            ?: $detectedProvince
            ?: ($data['province'] ?? null);

        if ($fullAddress !== '' && ! $locationSource && ! $district && ! $province) {
            throw ValidationException::withMessages([
                'full_address' => 'The address did not match an active delivery zone, configured district, or configured province. Select a zone or provide a district/province.',
            ]);
        }

        if (! $locationSource && $district) {
            $locationSource = 'entered district';
            $matchedLocation = $district;
        } elseif (! $locationSource && $province) {
            $locationSource = 'entered province';
            $matchedLocation = $province;
        }

        $estimate = isset($data['subtotal'])
            ? $deliveryFees->estimate(
                (float) $data['subtotal'],
                $district,
                $data['distance_meters'] ?? null,
                null,
                $province,
                null,
                $selectedZone?->id ?? 0,
            )
            : null;
        $simulation = $estimate ? [
            'service_charge' => $financialPolicy->serviceCharge((float) $data['subtotal']),
            'rider_earnings' => $financialPolicy->riderPayoutForDistance(((int) ($data['distance_meters'] ?? 0)) / 1000, now()),
            'zone_name' => $selectedZone?->name,
            'district' => $district,
            'province' => $province,
            'full_address' => $fullAddress,
            'distance_meters' => (int) ($data['distance_meters'] ?? 0),
            'location_source' => $locationSource,
            'matched_location' => $matchedLocation,
        ] : null;
        if ($simulation) {
            $simulation['customer_total'] = round((float) $data['subtotal'] + $estimate['delivery_fee'] + $simulation['service_charge'], 2);
            $simulation['platform_delivery_margin'] = round($estimate['delivery_fee'] + $simulation['service_charge'] - $simulation['rider_earnings'], 2);
        }

        return view('admin.delivery-engine.simulator', compact('estimate', 'simulation', 'zones', 'selectedZone'));
    }

    public function history(): View
    {
        return view('admin.delivery-engine.history', [
            'histories' => DeliveryRuleHistory::query()->with(['rule.zone', 'user'])->latest()->paginate(30),
        ]);
    }

    public function policies(FinancialPolicyService $financialPolicy): View
    {
        return $this->policiesView($financialPolicy, true);
    }

    public function adminPolicies(FinancialPolicyService $financialPolicy): View
    {
        return $this->policiesView($financialPolicy, false);
    }

    private function policiesView(FinancialPolicyService $financialPolicy, bool $financialControls): View
    {
        return view('admin.delivery-engine.policies', [
            'promotions' => DeliveryPromotion::query()->latest()->paginate(10, ['*'], 'promotions_page'),
            'freeRules' => FreeDeliveryRule::query()->orderBy('priority')->paginate(10, ['*'], 'free_rules_page'),
            'holidays' => DeliveryHoliday::query()->latest('starts_on')->paginate(10, ['*'], 'holidays_page'),
            'riderRules' => RiderPaymentRule::query()->orderBy('priority')->paginate(10, ['*'], 'rider_rules_page'),
            'financialPolicy' => $financialPolicy->settings(),
            'financialControls' => $financialControls,
            'routePrefix' => $financialControls ? 'super-admin.delivery' : 'admin.delivery',
        ]);
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        DeliveryPromotion::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'], 'type' => ['required', 'in:free_delivery,percentage_discount,vendor_sponsored'],
            'discount_percent' => ['nullable', 'numeric', 'between:0,100'], 'minimum_order' => ['required', 'numeric', 'min:0'],
            'starts_on' => ['nullable', 'date'], 'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'priority' => ['required', 'integer', 'min:0'], 'status' => ['required', 'in:active,inactive'],
        ]));

        return back()->with('status', 'Delivery promotion saved.');
    }

    public function storeFreeRule(Request $request): RedirectResponse
    {
        FreeDeliveryRule::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'], 'condition_type' => ['required', 'in:subtotal,first_order,weekend,coupon,premium_membership'],
            'minimum_order' => ['required', 'numeric', 'min:0'], 'coupon_code' => ['nullable', 'string', 'max:255'],
            'starts_on' => ['nullable', 'date'], 'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'priority' => ['required', 'integer', 'min:0'], 'status' => ['required', 'in:active,inactive'],
        ]));

        return back()->with('status', 'Free-delivery rule saved.');
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        DeliveryHoliday::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'], 'extra_charge' => ['required', 'numeric', 'min:0'],
            'starts_on' => ['required', 'date'], 'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'reason' => ['nullable', 'string', 'max:255'], 'status' => ['required', 'in:active,inactive'],
        ]));

        return back()->with('status', 'Holiday pricing rule saved.');
    }

    public function storeRiderRule(Request $request): RedirectResponse
    {
        RiderPaymentRule::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'], 'base_pay' => ['required', 'numeric', 'min:0'],
            'per_km_bonus' => ['required', 'numeric', 'min:0'], 'peak_hour_bonus' => ['required', 'numeric', 'min:0'],
            'rain_bonus' => ['required', 'numeric', 'min:0'], 'holiday_bonus' => ['required', 'numeric', 'min:0'], 'night_bonus' => ['required', 'numeric', 'min:0'],
            'peak_start_hour' => ['nullable', 'integer', 'between:0,23'], 'peak_end_hour' => ['nullable', 'integer', 'between:0,23'],
            'night_start_hour' => ['nullable', 'integer', 'between:0,23'], 'night_end_hour' => ['nullable', 'integer', 'between:0,23'],
            'starts_on' => ['nullable', 'date'], 'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'priority' => ['required', 'integer', 'min:0'], 'status' => ['required', 'in:active,inactive'],
        ]));

        return back()->with('status', 'Rider payment rule saved.');
    }

    public function updateServiceCharge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_charge_rate_percent' => ['required', 'numeric', 'between:0,100'],
            'service_charge_flat_amount' => ['nullable', 'numeric', 'min:0'],
            'service_charge_minimum' => ['nullable', 'numeric', 'min:0'],
            'service_charge_maximum' => ['nullable', 'numeric', 'min:0'],
        ]);

        Setting::putMany([
            'service_charge_rate_percent' => $data['service_charge_rate_percent'],
            'service_charge_flat_amount' => $data['service_charge_flat_amount'] ?? 0,
            'service_charge_minimum' => $data['service_charge_minimum'] ?? 0,
            'service_charge_maximum' => $data['service_charge_maximum'] ?? 0,
        ]);

        return back()->with('status', 'Service-charge policy saved.');
    }

    public function destroyPolicy(string $type, int $id): RedirectResponse
    {
        $model = match ($type) {
            'promotion' => DeliveryPromotion::class,
            'free-rule' => FreeDeliveryRule::class,
            'holiday' => DeliveryHoliday::class,
            'rider-rule' => RiderPaymentRule::class,
            default => abort(404),
        };

        $model::query()->findOrFail($id)->delete();

        return back()->with('status', 'Delivery policy deleted. Existing orders are unchanged.');
    }

    public function analytics(): View
    {
        return view('admin.delivery-engine.analytics', [
            'summary' => [
                'active_zones' => Zone::query()->where('status', 'active')->count(),
                'active_rules' => DeliveryPricingRule::query()->where('status', 'active')->count(),
                'completed_deliveries' => Delivery::query()->where('status', 'delivered')->count(),
                'average_delivery_minutes' => round((float) Delivery::query()->whereNotNull('picked_up_at')->whereNotNull('delivered_at')->avg(DB::raw('TIMESTAMPDIFF(MINUTE, picked_up_at, delivered_at)')), 1),
            ],
        ]);
    }

    /**
     * Resolve a typed address using the simulator's required priority:
     * active zone, configured district, then configured province.
     *
     * @param Collection<int, Zone> $zones
     * @return array{zone: Zone|null, district: string|null, province: string|null, source: string|null, matched_location: string|null}
     */
    private function detectSimulatorLocation(string $address, Collection $zones): array
    {
        $zone = $zones
            ->sortByDesc(fn (Zone $candidate) => mb_strlen($this->normalizeLocationText($candidate->name)))
            ->first(fn (Zone $candidate) => $this->addressContainsLocation($address, $candidate->name));

        if ($zone) {
            return [
                'zone' => $zone,
                'district' => $zone->district,
                'province' => $zone->province,
                'source' => 'address zone',
                'matched_location' => $zone->name,
            ];
        }

        $districts = $zones->pluck('district')
            ->merge(
                DeliveryPricingRule::query()
                    ->where('status', 'active')
                    ->where('scope', 'district')
                    ->pluck('district')
            )
            ->merge(
                DeliveryFee::query()
                    ->where('status', 'active')
                    ->whereNotIn('district', ['All Districts', 'Default', '*'])
                    ->pluck('district')
            );
        $district = $this->firstAddressLocationMatch($address, $districts);

        if ($district) {
            $province = $zones->first(
                fn (Zone $candidate) => strcasecmp((string) $candidate->district, $district) === 0
            )?->province;

            return [
                'zone' => null,
                'district' => $district,
                'province' => $province,
                'source' => 'address district',
                'matched_location' => $district,
            ];
        }

        $provinces = $zones->pluck('province')
            ->merge(
                DeliveryPricingRule::query()
                    ->where('status', 'active')
                    ->where('scope', 'province')
                    ->pluck('province')
            );
        $province = $this->firstAddressLocationMatch($address, $provinces);

        return [
            'zone' => null,
            'district' => null,
            'province' => $province,
            'source' => $province ? 'address province' : null,
            'matched_location' => $province,
        ];
    }

    /** @param Collection<int, string|null> $locations */
    private function firstAddressLocationMatch(string $address, Collection $locations): ?string
    {
        return $locations
            ->filter(fn ($location) => is_string($location) && trim($location) !== '')
            ->unique(fn (string $location) => $this->normalizeLocationText($location))
            ->sortByDesc(fn (string $location) => mb_strlen($this->normalizeLocationText($location)))
            ->first(fn (string $location) => $this->addressContainsLocation($address, $location));
    }

    private function addressContainsLocation(string $address, string $location): bool
    {
        $normalizedAddress = ' '.$this->normalizeLocationText($address).' ';
        $normalizedLocation = $this->normalizeLocationText($location);

        return $normalizedLocation !== ''
            && str_contains($normalizedAddress, ' '.$normalizedLocation.' ');
    }

    private function normalizeLocationText(string $value): string
    {
        $normalized = Str::lower(Str::ascii($value));

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $normalized));
    }

    /** @return array<string, mixed> */
    private function zoneData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    /** @return array<string, mixed> */
    private function ruleData(Request $request): array
    {
        return $request->validate([
            'zone_id' => ['nullable', 'exists:zones,id'],
            'scope' => ['required', 'in:zone,district,province,default'],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'base_fee' => ['required', 'numeric', 'min:0'],
            'per_km_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'maximum_distance_km' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_minutes' => ['nullable', 'integer', 'min:1'],
            'priority' => ['required', 'integer', 'min:0'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    /** @param array<string, mixed> $changes */
    private function recordRuleHistory(Request $request, DeliveryPricingRule $rule, string $action, array $changes): void
    {
        DeliveryRuleHistory::query()->create([
            'delivery_pricing_rule_id' => $rule->id,
            'user_id' => $request->user()->id,
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}
