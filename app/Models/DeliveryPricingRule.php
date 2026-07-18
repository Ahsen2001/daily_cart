<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryPricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id', 'scope', 'district', 'province', 'base_fee', 'per_km_fee', 'minimum_order',
        'free_delivery_threshold', 'maximum_distance_km', 'estimated_delivery_minutes', 'priority',
        'starts_on', 'ends_on', 'status',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2', 'per_km_fee' => 'decimal:2', 'minimum_order' => 'decimal:2',
            'free_delivery_threshold' => 'decimal:2', 'maximum_distance_km' => 'decimal:2',
            'starts_on' => 'date', 'ends_on' => 'date',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
