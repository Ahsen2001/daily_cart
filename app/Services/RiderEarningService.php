<?php

namespace App\Services;

use App\Models\Rider;
use Illuminate\Support\Carbon;

class RiderEarningService
{
    public function __construct(private readonly FinancialPolicyService $financialPolicy) {}

    public function summary(Rider $rider, ?Carbon $at = null): array
    {
        $at ??= now();

        return [
            'daily' => $this->earnedBetween($rider, $at->copy()->startOfDay(), $at->copy()->endOfDay()),
            'weekly' => $this->earnedBetween($rider, $at->copy()->startOfWeek(), $at->copy()->endOfWeek()),
            'monthly' => $this->earnedBetween($rider, $at->copy()->startOfMonth(), $at->copy()->endOfMonth()),
        ];
    }

    public function total(Rider $rider): float
    {
        return round((float) $rider->deliveries()
            ->where('status', 'delivered')
            ->with('order')
            ->get()
            ->sum(fn ($delivery) => $delivery->rider_payout !== null
                ? (float) $delivery->rider_payout
                : $this->financialPolicy->riderPayout($delivery)), 2);
    }

    private function earnedBetween(Rider $rider, Carbon $from, Carbon $to): float
    {
        return round((float) $rider->deliveries()
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$from, $to])
            ->with('order')
            ->get()
            ->sum(fn ($delivery) => $delivery->rider_payout !== null
                ? (float) $delivery->rider_payout
                : $this->financialPolicy->riderPayout($delivery)), 2);
    }
}
