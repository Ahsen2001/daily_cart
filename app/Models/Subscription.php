<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'product_id',
        'vendor_id',
        'frequency',
        'quantity',
        'unit_price',
        'total_amount',
        'delivery_address',
        'preferred_delivery_time',
        'start_date',
        'end_date',
        'next_delivery_date',
        'payment_method',
        'notes',
        'last_generated_at',
        'failed_reason',
        'plan_name',
        'price',
        'currency',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_delivery_date' => 'date',
            'last_generated_at' => 'datetime',
            'price' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function generatedOrders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
