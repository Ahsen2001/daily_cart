<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryLocationRequest;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\DeliveryTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliveryTrackingController extends Controller
{
    public function __construct(private readonly DeliveryTrackingService $tracking) {}

    public function active(Request $request): JsonResponse
    {
        $rider = $request->user()->rider;
        abort_unless($rider, 404, 'Rider profile not found.');
        $delivery = $this->tracking->activeFor($rider);

        return response()->json([
            'active' => $delivery !== null,
            'delivery' => $delivery ? [
                'id' => $delivery->id,
                'status' => $delivery->status,
            ] : null,
        ]);
    }

    public function start(Request $request, Delivery $delivery): JsonResponse
    {
        $rider = $request->user()->rider;
        abort_unless($rider, 404, 'Rider profile not found.');
        $session = $this->tracking->start($delivery, $rider);

        return response()->json([
            'message' => 'Active delivery tracking started.',
            'delivery_id' => $delivery->id,
            'tracking_token' => $session['tracking_token'],
            'started_at' => $session['state']->started_at,
        ]);
    }

    public function store(
        StoreDeliveryLocationRequest $request,
        Delivery $delivery,
    ): JsonResponse {
        $location = $this->tracking->record(
            $delivery,
            $request->user()->rider,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Delivery location recorded.',
            'location' => $this->locationPayload($location),
        ], 201);
    }

    public function stop(Request $request, Delivery $delivery): JsonResponse
    {
        abort_unless($delivery->rider_id === $request->user()->rider?->id, 403);
        $this->tracking->stopDelivery($delivery, 'rider_stopped');

        return response()->json(['message' => 'Active delivery tracking stopped.']);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json($this->orderPayload($order));
    }

    public function stream(Request $request, Order $order): StreamedResponse
    {
        $this->authorize('view', $order);
        abort_unless($order->delivery, 404, 'Delivery tracking is not available yet.');

        return response()->stream(function () use ($order) {
            @set_time_limit(35);
            $lastVersion = null;

            for ($iteration = 0; $iteration < 15; $iteration++) {
                if (connection_aborted()) {
                    break;
                }

                $payload = $this->orderPayload($order->fresh());
                $version = data_get($payload, 'location.recorded_at').'|'.data_get($payload, 'tracking.status');
                if ($version !== $lastVersion) {
                    echo "event: location\n";
                    echo 'data: '.json_encode($payload, JSON_UNESCAPED_SLASHES)."\n\n";
                    $lastVersion = $version;
                } else {
                    echo ": heartbeat\n\n";
                }

                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    private function orderPayload(Order $order): array
    {
        $delivery = $order->delivery;
        $state = $delivery?->trackingState;
        $history = $delivery
            ? $delivery->locations()->latest('recorded_at')->limit(100)->get()->reverse()->values()
            : collect();

        return [
            'order_id' => $order->id,
            'delivery_id' => $delivery?->id,
            'destination' => $order->delivery_latitude !== null && $order->delivery_longitude !== null ? [
                'latitude' => (float) $order->delivery_latitude,
                'longitude' => (float) $order->delivery_longitude,
            ] : null,
            'tracking' => [
                'status' => $state?->status ?? 'unavailable',
                'is_active' => $state?->status === 'active'
                    && in_array($delivery?->status, DeliveryTrackingService::ACTIVE_STATUSES, true),
                'started_at' => $state?->started_at,
                'stopped_at' => $state?->stopped_at,
                'stop_reason' => $state?->stop_reason,
            ],
            'location' => $state?->recorded_at ? $this->locationPayload($state) : null,
            'history' => $history->map(fn ($location) => $this->locationPayload($location)),
        ];
    }

    private function locationPayload(object $location): array
    {
        return [
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'accuracy' => $location->accuracy !== null ? (float) $location->accuracy : null,
            'altitude' => $location->altitude !== null ? (float) $location->altitude : null,
            'speed' => $location->speed !== null ? (float) $location->speed : null,
            'heading' => $location->heading !== null ? (float) $location->heading : null,
            'recorded_at' => $location->recorded_at,
        ];
    }
}
