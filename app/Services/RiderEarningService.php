<?php

namespace App\Services;

use App\Models\Rider;
use Illuminate\Support\Carbon;

class RiderEarningService
{
    public function summary(Rider $rider): array
    {
        return [
            'daily' => $this->earnedBetween($rider, now()->startOfDay(), now()->endOfDay()),
            'weekly' => $this->earnedBetween($rider, now()->startOfWeek(), now()->endOfWeek()),
            'monthly' => $this->earnedBetween($rider, now()->startOfMonth(), now()->endOfMonth()),
        ];
    }

    private function earnedBetween(Rider $rider, Carbon $from, Carbon $to): float
    {
        return (float) $rider->deliveries()
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$from, $to])
            ->with('order')
            ->get()
            ->sum(fn ($delivery) => (float) $delivery->order?->delivery_fee);
    }
}
