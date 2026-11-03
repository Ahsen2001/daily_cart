<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class DeliveryLocationFreshness
{
    /** @return array{status:string,message:string,age_seconds:int|null,is_stale:bool,is_outdated:bool} */
    public function describe(?DateTimeInterface $recordedAt, bool $trackingActive, ?string $deliveryStatus): array
    {
        if (! $trackingActive) {
            $terminal = in_array($deliveryStatus, ['delivered', 'failed', 'cancelled', 'refunded'], true);

            return [
                'status' => $terminal ? 'completed' : 'waiting',
                'message' => $terminal
                    ? 'Delivery '.str_replace('_', ' ', (string) $deliveryStatus)
                    : 'Waiting for rider location',
                'age_seconds' => null,
                'is_stale' => false,
                'is_outdated' => false,
            ];
        }

        if (! $recordedAt) {
            return [
                'status' => 'waiting',
                'message' => 'Waiting for rider location',
                'age_seconds' => null,
                'is_stale' => false,
                'is_outdated' => false,
            ];
        }

        $ageSeconds = max(0, (int) Carbon::instance($recordedAt)->diffInSeconds(now()));
        $freshSeconds = max(1, (int) config('delivery_tracking.fresh_seconds', 90));
        $outdatedSeconds = max($freshSeconds, (int) config('delivery_tracking.outdated_seconds', 180));

        if ($ageSeconds < $freshSeconds) {
            return [
                'status' => 'live',
                'message' => 'Live · updated just now',
                'age_seconds' => $ageSeconds,
                'is_stale' => false,
                'is_outdated' => false,
            ];
        }

        if ($ageSeconds <= $outdatedSeconds) {
            $minutes = max(1, (int) floor($ageSeconds / 60));

            return [
                'status' => 'recent',
                'message' => 'Last updated '.$minutes.' '.($minutes === 1 ? 'minute' : 'minutes').' ago',
                'age_seconds' => $ageSeconds,
                'is_stale' => true,
                'is_outdated' => false,
            ];
        }

        return [
            'status' => 'outdated',
            'message' => 'Location may be outdated',
            'age_seconds' => $ageSeconds,
            'is_stale' => true,
            'is_outdated' => true,
        ];
    }
}
