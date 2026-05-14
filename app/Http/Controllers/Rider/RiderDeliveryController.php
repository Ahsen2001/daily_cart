<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryProofRequest;
use App\Http\Requests\FailedDeliveryRequest;
use App\Http\Requests\RiderLocationRequest;
use App\Models\Delivery;
use App\Services\DeliveryService;
use App\Services\RiderEarningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderDeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $deliveries = $request->user()->rider->deliveries()
            ->with(['order.customer.user', 'order.vendor'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return view('rider.deliveries.index', compact('deliveries'));
    }

    public function show(Delivery $delivery): View
    {
        $this->authorize('view', $delivery);

        return view('rider.deliveries.show', [
            'delivery' => $delivery->load(['order.customer.user', 'order.vendor', 'proofs']),
        ]);
    }

    public function accept(Delivery $delivery, DeliveryService $deliveries): RedirectResponse
    {
        $this->authorize('update', $delivery);
        $deliveries->accept($delivery);

        return back()->with('status', 'Delivery accepted.');
    }

    public function pickedUp(Delivery $delivery, DeliveryService $deliveries): RedirectResponse
    {
        $this->authorize('update', $delivery);
        $deliveries->markPickedUp($delivery);

        return back()->with('status', 'Delivery marked as picked up.');
    }

    public function onTheWay(Delivery $delivery, DeliveryService $deliveries): RedirectResponse
    {
        $this->authorize('update', $delivery);
        $deliveries->markOnTheWay($delivery);

        return back()->with('status', 'Delivery marked as on the way.');
    }

    public function delivered(DeliveryProofRequest $request, Delivery $delivery, DeliveryService $deliveries): RedirectResponse
    {
        $deliveries->markDelivered(
            $delivery,
            $request->file('proof_image'),
            $request->file('customer_signature'),
            $request->note
        );

        return back()->with('status', 'Delivery completed.');
    }

    public function failed(FailedDeliveryRequest $request, Delivery $delivery, DeliveryService $deliveries): RedirectResponse
    {
        $deliveries->markFailed($delivery, $request->failed_reason);

        return back()->with('status', 'Delivery marked as failed.');
    }

    public function location(RiderLocationRequest $request): RedirectResponse
    {
        $request->user()->rider->locations()->create([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'recorded_at' => now(),
        ]);

        return back()->with('status', 'Location updated.');
    }

    public function earnings(Request $request, RiderEarningService $earnings): View
    {
        return view('rider.deliveries.earnings', [
            'summary' => $earnings->summary($request->user()->rider),
        ]);
    }
}
