<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'name', 'delivery_fee', 'status'];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
