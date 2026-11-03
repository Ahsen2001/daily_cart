<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryLocationBatchRequest;
use App\Http\Requests\StoreDeliveryLocationRequest;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\DeliveryLocationFreshness;
use App\Services\DeliveryTrackingRelationLoader;
use App\Services\DeliveryTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingService $tracking,
        private readonly DeliveryLocationFreshness $freshness,
        private readonly DeliveryTrackingRelationLoader $trackingRelations,
    ) {}

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

    public function storeBatch(
        StoreDeliveryLocationBatchRequest $request,
        Delivery $delivery,
    ): JsonResponse {
        $validated = $request->validated();
        $result = $this->tracking->recordBatch(
            $delivery,
            $request->user()->rider,
            $validated['tracking_token'],
            $validated['points'],
        );

        return response()->json([
            'message' => 'Delivery location batch processed.',
            ...$result,
        ], 202);
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
        $this->trackingRelations->load($order);
        $delivery = $order->delivery;
        $state = $delivery?->trackingState;
        $isActive = $delivery
            && $state?->status === 'active'
            && in_array($delivery->status, DeliveryTrackingService::ACTIVE_STATUSES, true)
            && ! in_array($order->order_status, ['cancelled', 'refunded', 'delivered'], true);
        $current = $isActive ? $delivery->currentLocation : null;
        $terminalStatus = in_array($order->order_status, ['delivered', 'cancelled', 'refunded'], true)
            ? $order->order_status
            : $delivery?->status;
        $freshness = $this->freshness->describe($current?->recorded_at, (bool) $isActive, $terminalStatus);

        return [
            'order_id' => $order->id,
            'delivery_id' => $delivery?->id,
            'order_status' => $order->order_status,
            'delivery_status' => $delivery?->status,
            'rider_display_name' => $delivery?->rider?->user?->name,
            'destination' => [
                'address' => $order->delivery_address,
                'latitude' => $order->delivery_latitude !== null
                    ? (float) $order->delivery_latitude
                    : null,
                'longitude' => $order->delivery_longitude !== null
                    ? (float) $order->delivery_longitude
                    : null,
            ],
            'scheduled_delivery_time' => $order->scheduled_delivery_at ?? $delivery?->scheduled_at,
            'freshness' => $freshness,
            'tracking' => [
                'status' => $state?->status ?? 'unavailable',
                'is_active' => (bool) $isActive,
                'started_at' => $state?->started_at,
                'stopped_at' => $state?->stopped_at,
                'stop_reason' => $state?->stop_reason,
            ],
            'location' => $current ? [
                ...$this->locationPayload($current),
                'age_seconds' => $freshness['age_seconds'],
                'is_stale' => $freshness['is_stale'],
                'is_outdated' => $freshness['is_outdated'],
            ] : null,
        ];
    }

    private function locationPayload(object $location): array
    {
        $accuracy = data_get($location, 'accuracy_meters') ?? data_get($location, 'accuracy');

        return [
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'accuracy' => $accuracy !== null ? (float) $accuracy : null,
            'altitude' => data_get($location, 'altitude') !== null
                ? (float) data_get($location, 'altitude')
                : null,
            'speed' => $location->speed !== null ? (float) $location->speed : null,
            'heading' => $location->heading !== null ? (float) $location->heading : null,
            'recorded_at' => $location->recorded_at,
        ];
    }
}
