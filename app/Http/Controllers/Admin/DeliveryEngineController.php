<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPricingRule;
use App\Models\DeliveryRuleHistory;
use App\Models\Delivery;
use App\Models\Zone;
use App\Services\DeliveryFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function rules(): View
    {
        return view('admin.delivery-engine.rules', [
            'rules' => DeliveryPricingRule::query()->with('zone')->orderBy('priority')->paginate(20),
            'zones' => Zone::query()->where('status', 'active')->orderBy('name')->get(),
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

    public function simulator(Request $request, DeliveryFeeService $deliveryFees): View
    {
        $data = $request->validate([
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'distance_meters' => ['nullable', 'integer', 'min:0'],
        ]);
        $estimate = isset($data['subtotal'])
            ? $deliveryFees->estimate((float) $data['subtotal'], $data['district'] ?? null, $data['distance_meters'] ?? null, null, $data['province'] ?? null)
            : null;

        return view('admin.delivery-engine.simulator', compact('estimate'));
    }

    public function history(): View
    {
        return view('admin.delivery-engine.history', [
            'histories' => DeliveryRuleHistory::query()->with(['rule.zone', 'user'])->latest()->paginate(30),
        ]);
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
