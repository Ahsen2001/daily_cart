<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreeDeliveryRule extends Model
{
    protected $fillable = ['name', 'condition_type', 'minimum_order', 'coupon_code', 'starts_on', 'ends_on', 'priority', 'status'];

    protected function casts(): array
    {
        return ['minimum_order' => 'decimal:2', 'starts_on' => 'date', 'ends_on' => 'date'];
    }
}
