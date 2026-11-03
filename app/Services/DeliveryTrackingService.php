<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryTrackingState;
use App\Models\Rider;
use App\Models\RiderLocation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeliveryTrackingService
{
    public const ACTIVE_STATUSES = ['accepted', 'picked_up', 'on_the_way'];

    public function start(Delivery $delivery, Rider $rider): array
    {
        $this->assertTrackable($delivery, $rider);
        $token = Str::random(64);

        $state = DeliveryTrackingState::query()->updateOrCreate(
            ['delivery_id' => $delivery->id],
            [
                'rider_id' => $rider->id,
                'status' => 'active',
                'tracking_token_hash' => hash('sha256', $token),
                'started_at' => now(),
                'stopped_at' => null,
                'stop_reason' => null,
            ],
        );

        return ['state' => $state->refresh(), 'tracking_token' => $token];
    }

    public function record(Delivery $delivery, Rider $rider, array $data): RiderLocation
    {
        return DB::transaction(function () use ($delivery, $rider, $data) {
            $lockedDelivery = Delivery::query()->lockForUpdate()->findOrFail($delivery->id);
            $this->assertTrackable($lockedDelivery, $rider);

            $state = DeliveryTrackingState::query()
                ->where('delivery_id', $lockedDelivery->id)
                ->lockForUpdate()
                ->first();

            $providedHash = hash('sha256', (string) $data['tracking_token']);
            abort_unless(
                $state
                    && $state->status === 'active'
                    && $state->rider_id === $rider->id
                    && filled($state->tracking_token_hash)
                    && hash_equals($state->tracking_token_hash, $providedHash),
                403,
                'This tracking session is no longer authorized.',
            );

            $recordedAt = isset($data['recorded_at'])
                ? Carbon::parse($data['recorded_at'])
                : now();
            if ($recordedAt->isAfter(now()->addMinutes(2)) || $recordedAt->isBefore(now()->subMinutes(15))) {
                throw ValidationException::withMessages([
                    'recorded_at' => 'The location timestamp is outside the accepted tracking window.',
                ]);
            }

            $this->validatePositionQuality($lockedDelivery, $data, $recordedAt);

            $payload = [
                'rider_id' => $rider->id,
                'delivery_id' => $lockedDelivery->id,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy' => $data['accuracy'] ?? null,
                'altitude' => $data['altitude'] ?? null,
                'speed' => $data['speed'] ?? null,
                'heading' => $data['heading'] ?? null,
                'recorded_at' => $recordedAt,
            ];

            $location = RiderLocation::query()->create($payload);
            $state->update([
                ...collect($payload)->only([
                    'latitude', 'longitude', 'accuracy', 'altitude', 'speed', 'heading', 'recorded_at',
                ])->all(),
                'status' => 'active',
            ]);

            return $location;
        });
    }

    public function stopDelivery(Delivery $delivery, string $reason): void
    {
        DeliveryTrackingState::query()
            ->where('delivery_id', $delivery->id)
            ->where('status', 'active')
            ->update([
                'status' => 'stopped',
                'tracking_token_hash' => null,
                'stopped_at' => now(),
                'stop_reason' => Str::limit($reason, 80, ''),
                'updated_at' => now(),
            ]);
    }

    public function stopRider(Rider $rider, string $reason): void
    {
        DeliveryTrackingState::query()
            ->where('rider_id', $rider->id)
            ->where('status', 'active')
            ->update([
                'status' => 'stopped',
                'tracking_token_hash' => null,
                'stopped_at' => now(),
                'stop_reason' => Str::limit($reason, 80, ''),
                'updated_at' => now(),
            ]);
    }

    public function activeFor(Rider $rider): ?Delivery
    {
        return $rider->deliveries()
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereHas('order', fn ($query) => $query->whereNotIn('order_status', ['cancelled', 'refunded', 'delivered']))
            ->latest('accepted_at')
            ->first();
    }

    private function assertTrackable(Delivery $delivery, Rider $rider): void
    {
        $delivery->loadMissing('order');
        abort_unless($delivery->rider_id === $rider->id, 403, 'This delivery is assigned to another rider.');
        abort_unless(
            in_array($delivery->status, self::ACTIVE_STATUSES, true),
            422,
            'Location tracking is allowed only for an accepted active delivery.',
        );
        abort_unless($rider->verification_status === 'verified', 403, 'Rider authorization is no longer valid.');
        abort_if(
            in_array($delivery->order?->order_status, ['cancelled', 'refunded', 'delivered'], true),
            422,
            'This order no longer permits active delivery tracking.',
        );
    }

    private function validatePositionQuality(Delivery $delivery, array $data, Carbon $recordedAt): void
    {
        if (isset($data['accuracy']) && (float) $data['accuracy'] > 100) {
            throw ValidationException::withMessages(['accuracy' => 'GPS accuracy is too poor for delivery tracking.']);
        }
        if (isset($data['speed']) && (float) $data['speed'] > 70) {
            throw ValidationException::withMessages(['speed' => 'The reported GPS speed is not plausible.']);
        }

        $previous = RiderLocation::query()
            ->where('delivery_id', $delivery->id)
            ->latest('recorded_at')
            ->first();
        if (! $previous || ! $previous->recorded_at || ! $recordedAt->isAfter($previous->recorded_at)) {
            return;
        }

        $seconds = $previous->recorded_at->diffInMilliseconds($recordedAt) / 1000;
        if ($seconds <= 0) {
            return;
        }

        $earthRadius = 6371000;
        $lat1 = deg2rad((float) $previous->latitude);
        $lat2 = deg2rad((float) $data['latitude']);
        $latDelta = deg2rad((float) $data['latitude'] - (float) $previous->latitude);
        $lngDelta = deg2rad((float) $data['longitude'] - (float) $previous->longitude);
        $a = sin($latDelta / 2) ** 2
            + cos($lat1) * cos($lat2) * sin($lngDelta / 2) ** 2;
        $a = max(0, min(1, $a));
        $distance = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));

        if ($distance / $seconds > 70) {
            throw ValidationException::withMessages([
                'location' => 'The GPS update was ignored because it represents an impossible jump.',
            ]);
        }
    }
}
