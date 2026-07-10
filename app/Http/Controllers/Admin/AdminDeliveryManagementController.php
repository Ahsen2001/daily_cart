<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryFee;
use App\Models\DeliverySchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDeliveryManagementController extends Controller
{
    // ==========================================
    // DELIVERY FEES CRUD
    // ==========================================

    public function feesIndex(Request $request): View
    {
        $fees = DeliveryFee::latest()->paginate(15);
        return view('admin.deliveries.fees.index', compact('fees'));
    }

    public function feesCreate(): View
    {
        return view('admin.deliveries.fees.create');
    }

    public function feesStore(Request $request): RedirectResponse
    {
        $request->validate([
            'district' => ['required', 'string', 'unique:delivery_fees'],
            'base_fee' => ['required', 'numeric', 'min:0'],
            'per_km_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0'],
            'free_delivery_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DeliveryFee::create($request->all());

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Delivery fee configuration added.');
    }

    public function feesEdit(DeliveryFee $fee): View
    {
        return view('admin.deliveries.fees.edit', compact('fee'));
    }

    public function feesUpdate(Request $request, DeliveryFee $fee): RedirectResponse
    {
        $request->validate([
            'district' => ['required', 'string', 'unique:delivery_fees,district,' . $fee->id],
            'base_fee' => ['required', 'numeric', 'min:0'],
            'per_km_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0'],
            'free_delivery_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $fee->update($request->all());

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Delivery fee configuration updated.');
    }

    public function feesDestroy(DeliveryFee $fee): RedirectResponse
    {
        $fee->delete();
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
