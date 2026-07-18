<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryHoliday extends Model
{
    protected $fillable = ['name', 'extra_charge', 'starts_on', 'ends_on', 'reason', 'status'];

    protected function casts(): array
    {
        return ['extra_charge' => 'decimal:2', 'starts_on' => 'date', 'ends_on' => 'date'];
    }
}
