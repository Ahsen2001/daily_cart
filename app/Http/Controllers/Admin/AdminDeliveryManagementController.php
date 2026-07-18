<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDeliveryManagementController extends Controller
{
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
