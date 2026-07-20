<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    public function __construct(private readonly DeliveryService $deliveryService) {}

    public function index(Request $request): JsonResponse
    {
        $rider = $request->user()->rider;
        if (! $rider) {
            return response()->json(['message' => 'Rider profile not found.'], 404);
        }

        $deliveries = $rider->deliveries()
            ->with(['order.customer.user', 'order.vendor'])
            ->latest('scheduled_at')
            ->paginate(15);

        return response()->json([
            'deliveries' => $deliveries,
        ]);
    }

    public function show(Delivery $delivery, Request $request): JsonResponse
    {
        $rider = $request->user()->rider;
        if (! $rider || $delivery->rider_id !== $rider->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return response()->json([
            'delivery' => $delivery->load(['order.customer.user', 'order.vendor', 'proofs']),
        ]);
    }

    public function updateStatus(Request $request, Delivery $delivery): JsonResponse
    {
        $rider = $request->user()->rider;
        if (! $rider || $delivery->rider_id !== $rider->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:accepted,picked_up,on_the_way,delivered,failed'],
            'failed_reason' => ['required_if:status,failed', 'string'],
            'proof_image' => ['required_if:status,delivered', 'image'],
            'customer_signature' => ['nullable', 'image'],
            'note' => ['nullable', 'string'],
        ]);

        switch ($request->status) {
            case 'accepted':
                $this->deliveryService->accept($delivery, $request->user());
                break;
            case 'picked_up':
                $this->deliveryService->markPickedUp($delivery, $request->user());
                break;
            case 'on_the_way':
                $this->deliveryService->markOnTheWay($delivery, $request->user());
                break;
            case 'delivered':
                $this->deliveryService->markDelivered(
                    $delivery,
                    $request->file('proof_image'),
                    $request->file('customer_signature'),
                    $request->note,
                    $request->user(),
                );
                break;
            case 'failed':
                $this->deliveryService->markFailed($delivery, $request->failed_reason, $request->user());
                break;
        }

        return response()->json([
            'message' => 'Delivery status updated successfully.',
            'delivery' => $delivery->refresh()->load(['order.customer.user', 'order.vendor', 'proofs']),
        ]);
    }

    public function location(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $rider = $request->user()->rider;
        if (! $rider) {
            return response()->json(['message' => 'Rider profile not found.'], 404);
        }

        $rider->locations()->create([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Location logged successfully.',
        ]);
    }
}
