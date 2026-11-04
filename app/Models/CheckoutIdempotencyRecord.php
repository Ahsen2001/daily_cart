<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutIdempotencyRecord extends Model
{
    protected $fillable = [
        'customer_id',
        'idempotency_key',
        'request_fingerprint',
        'request_snapshot',
        'status',
        'order_ids',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'request_snapshot' => 'array',
            'order_ids' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
