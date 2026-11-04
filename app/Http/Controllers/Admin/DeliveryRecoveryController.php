<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRecoveryCase;
use App\Models\Rider;
use App\Services\DeliveryRecoveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryRecoveryController extends Controller
{
    public function index(Request $request): View
    {
        $cases = DeliveryRecoveryCase::query()
            ->with(['order.customer.user', 'order.vendor', 'delivery.rider.user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByRaw("CASE WHEN escalated_at IS NOT NULL THEN 0 ELSE 1 END")
            ->orderBy('escalation_due_at')->paginate(20)->withQueryString();

        return view('admin.delivery-recoveries-index', compact('cases'));
    }

    public function show(DeliveryRecoveryCase $deliveryRecovery): View
    {
        return view('admin.delivery-recoveries-show', [
            'case' => $deliveryRecovery->load(['order.customer.user', 'order.vendor', 'order.payment', 'delivery.rider.user', 'events.actor', 'refund']),
            'riders' => Rider::with('user')->where('verification_status', 'verified')->orderBy('id')->get(),
        ]);
    }

    public function retry(Request $request, DeliveryRecoveryCase $deliveryRecovery, DeliveryRecoveryService $service): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $service->retry($deliveryRecovery, $request->user(), $data['note'] ?? null);
        return back()->with('status', 'Delivery retry started.');
    }

    public function reassign(Request $request, DeliveryRecoveryCase $deliveryRecovery, DeliveryRecoveryService $service): RedirectResponse
    {
        $data = $request->validate(['rider_id' => ['required', 'integer', 'exists:riders,id'], 'note' => ['nullable', 'string', 'max:1000']]);
        $service->reassign($deliveryRecovery, Rider::findOrFail($data['rider_id']), $request->user(), $data['note'] ?? null);
        return back()->with('status', 'Delivery reassigned.');
    }

    public function startReturn(Request $request, DeliveryRecoveryCase $deliveryRecovery, DeliveryRecoveryService $service): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $service->startReturn($deliveryRecovery, $request->user(), $data['note'] ?? null);
        return back()->with('status', 'Return to vendor started.');
    }

    public function completeReturn(Request $request, DeliveryRecoveryCase $deliveryRecovery, DeliveryRecoveryService $service): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $service->completeReturn($deliveryRecovery, $request->user(), $data['note'] ?? null);
        return back()->with('status', 'Return to vendor completed.');
    }

    public function approveRefund(Request $request, DeliveryRecoveryCase $deliveryRecovery, DeliveryRecoveryService $service): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01'], 'note' => ['nullable', 'string', 'max:1000']]);
        $service->approveRefund($deliveryRecovery, $request->user(), (float) $data['amount'], $data['note'] ?? null);
        return back()->with('status', 'Refund approved and recovery resolved.');
    }

    public function rejectRefund(Request $request, DeliveryRecoveryCase $deliveryRecovery, DeliveryRecoveryService $service): RedirectResponse
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:1000']]);
        $service->rejectRefund($deliveryRecovery, $request->user(), $data['note']);
        return back()->with('status', 'Refund rejected and recovery resolved.');
    }
}
