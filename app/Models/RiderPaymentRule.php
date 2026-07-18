<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderPaymentRule extends Model
{
    protected $fillable = ['name', 'base_pay', 'per_km_bonus', 'peak_hour_bonus', 'rain_bonus', 'holiday_bonus', 'night_bonus', 'peak_start_hour', 'peak_end_hour', 'night_start_hour', 'night_end_hour', 'starts_on', 'ends_on', 'priority', 'status'];

    protected function casts(): array
    {
        return [
            'base_pay' => 'decimal:2', 'per_km_bonus' => 'decimal:2', 'peak_hour_bonus' => 'decimal:2',
            'rain_bonus' => 'decimal:2', 'holiday_bonus' => 'decimal:2', 'night_bonus' => 'decimal:2',
            'starts_on' => 'date', 'ends_on' => 'date',
        ];
    }
}
