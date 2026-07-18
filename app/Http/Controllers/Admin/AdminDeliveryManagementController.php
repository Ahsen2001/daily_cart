<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryFee;
use App\Models\DeliverySchedule;
use App\Models\Setting;
use App\Services\OrderService;
use App\Services\FinancialPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDeliveryManagementController extends Controller
{
    // ==========================================
    // DELIVERY FEES CRUD
    // ==========================================

    public function feesIndex(FinancialPolicyService $financialPolicy): View
    {
        $fees = DeliveryFee::query()->latest('created_at')->paginate(15);

        return view('admin.deliveries.fees.index', [
            'fees' => $fees,
            'serviceChargeRatePercent' => OrderService::serviceChargeRate() * 100,
            'financialPolicy' => $financialPolicy->settings(),
        ]);
    }

    public function updateServiceCharge(Request $request, FinancialPolicyService $financialPolicy): RedirectResponse
    {
        $validated = $request->validate([
            'service_charge_rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_charge_flat_amount' => ['nullable', 'numeric', 'min:0'],
            'service_charge_minimum' => ['nullable', 'numeric', 'min:0'],
            'service_charge_maximum' => ['nullable', 'numeric', 'min:0'],
        ]);
        $policy = $financialPolicy->settings();

        Setting::putMany([
            'service_charge_rate_percent' => number_format((float) $validated['service_charge_rate_percent'], 2, '.', ''),
            'service_charge_flat_amount' => number_format((float) ($validated['service_charge_flat_amount'] ?? $policy['service_charge_flat_amount']), 2, '.', ''),
            'service_charge_minimum' => number_format((float) ($validated['service_charge_minimum'] ?? $policy['service_charge_minimum']), 2, '.', ''),
            'service_charge_maximum' => number_format((float) ($validated['service_charge_maximum'] ?? $policy['service_charge_maximum']), 2, '.', ''),
        ]);

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Service charge configuration updated.');
    }

    public function updateRiderPayout(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()->isSuperAdmin() || $request->user()->can('delivery.rider_payouts.manage'),
            403,
        );

        $validated = $request->validate([
            'rider_payout_base' => ['required', 'numeric', 'min:0'],
            'rider_payout_per_km' => ['required', 'numeric', 'min:0'],
            'rider_peak_bonus' => ['required', 'numeric', 'min:0'],
            'rider_peak_start_hour' => ['required', 'integer', 'between:0,23'],
            'rider_peak_end_hour' => ['required', 'integer', 'between:0,23'],
        ]);

        Setting::putMany($validated);

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Rider payout rules updated.');
    }

    public function updateDeliveryPromotion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_promotion_discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_promotion_minimum_subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        Setting::putMany($validated);

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Delivery promotion updated.');
    }

    public function updateDefaultVendorCommission(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()->isSuperAdmin() || $request->user()->can('finance.commissions.manage'),
            403,
        );

        $validated = $request->validate([
            'default_vendor_commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::putMany($validated);

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Default vendor commission updated.');
    }

    public function feesCreate(): View
    {
        return view('admin.deliveries.fees.create');
    }

    public function feesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'district' => ['required', 'string', 'max:255', 'unique:delivery_fees'],
            'base_fee' => ['required', 'numeric', 'min:0'],
            'per_km_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0'],
            'free_delivery_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $validated['district'] = trim($validated['district']);

        DeliveryFee::query()->create($validated);

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Delivery fee configuration added.');
    }

    public function feesEdit(DeliveryFee $fee): View
    {
        return view('admin.deliveries.fees.edit', compact('fee'));
    }

    public function feesUpdate(Request $request, DeliveryFee $fee): RedirectResponse
    {
        $validated = $request->validate([
            'district' => ['required', 'string', 'max:255', 'unique:delivery_fees,district,'.$fee->id],
            'base_fee' => ['required', 'numeric', 'min:0'],
            'per_km_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0'],
            'free_delivery_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $validated['district'] = trim($validated['district']);

        $fee->update($validated);

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Delivery fee configuration updated.');
    }

    public function feesDestroy(DeliveryFee $fee): RedirectResponse
    {
        DeliveryFee::query()->whereKey($fee->getKey())->delete();

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Delivery fee configuration deleted.');
    }

    // ==========================================
    // DELIVERY SCHEDULES
    // ==========================================

    public function schedulesIndex(): View
    {
        $schedules = DeliverySchedule::with('order.customer.user')->latest()->paginate(20);

        return view('admin.deliveries.schedules.index', compact('schedules'));
    }

    public function schedulesUpdate(Request $request, DeliverySchedule $schedule): RedirectResponse
    {
        $request->validate([
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['nullable', 'string'],
            'delivery_window' => ['nullable', 'string'],
            'status' => ['required', 'string'],
        ]);

        $schedule->update($request->only(['scheduled_date', 'scheduled_time', 'delivery_window', 'status']));

        return redirect()->route('admin.delivery-schedules.index')->with('status', 'Delivery schedule updated successfully.');
    }
}
