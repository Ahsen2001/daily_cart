<?php

namespace App\Services;

use App\Models\Rider;
use Illuminate\Support\Carbon;

class RiderEarningService
{
    public function summary(Rider $rider, ?Carbon $at = null): array
    {
        $at ??= now();

        return [
            'daily' => $this->earnedBetween($rider, $at->copy()->startOfDay(), $at->copy()->endOfDay()),
            'weekly' => $this->earnedBetween($rider, $at->copy()->startOfWeek(), $at->copy()->endOfWeek()),
            'monthly' => $this->earnedBetween($rider, $at->copy()->startOfMonth(), $at->copy()->endOfMonth()),
        ];
    }

    private function earnedBetween(Rider $rider, Carbon $from, Carbon $to): float
    {
        return (float) $rider->deliveries()
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$from, $to])
            ->count() * FinanceReportService::RIDER_DELIVERY_EARNING;
    }
}
