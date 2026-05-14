<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class DeliveryScheduleService
{
    public const MINIMUM_LEAD_MINUTES = 30;
    public const ERROR_MESSAGE = 'Delivery time must be at least 30 minutes after placing the order.';

    public function minimumDeliveryTime(?CarbonInterface $placedAt = null): CarbonInterface
    {
        return ($placedAt ?? Carbon::now())->copy()->addMinutes(self::MINIMUM_LEAD_MINUTES);
    }

    public function isAllowed(CarbonInterface $scheduledAt, ?CarbonInterface $placedAt = null): bool
    {
        return $scheduledAt->greaterThanOrEqualTo($this->minimumDeliveryTime($placedAt));
    }

    public function validationRule(?CarbonInterface $placedAt = null): string
    {
        return 'after_or_equal:'.$this->minimumDeliveryTime($placedAt)->toDateTimeString();
    }
}
