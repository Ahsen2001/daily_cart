<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryPromotion extends Model
{
    protected $fillable = ['vendor_id', 'name', 'type', 'discount_percent', 'minimum_order', 'starts_on', 'ends_on', 'priority', 'status'];

    protected function casts(): array
    {
        return ['discount_percent' => 'decimal:2', 'minimum_order' => 'decimal:2', 'starts_on' => 'date', 'ends_on' => 'date'];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
