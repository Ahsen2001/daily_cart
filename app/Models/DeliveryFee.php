<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'district',
        'base_fee',
        'per_km_fee',
        'minimum_order',
        'free_delivery_limit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'per_km_fee' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'free_delivery_limit' => 'decimal:2',
        ];
    }
}
