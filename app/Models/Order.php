<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'vendor_id',
        'coupon_id',
        'subscription_id',
        'delivery_schedule_id',
        'subtotal',
        'discount_amount',
        'loyalty_points_redeemed',
        'loyalty_discount_amount',
        'delivery_fee',
        'service_charge',
        'tax_amount',
        'total_amount',
        'currency',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_distance_meters',
        'cancellation_reason',
        'order_status',
        'payment_status',
        'placed_at',
        'scheduled_delivery_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'loyalty_discount_amount' => 'decimal:2',
            'loyalty_points_redeemed' => 'integer',
            'delivery_fee' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivery_distance_meters' => 'integer',
            'placed_at' => 'datetime',
            'scheduled_delivery_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Order $order) {
            $order->statusHistories()->create([
                'status' => 'pending',
                'remarks' => 'Order placed successfully.',
                'updated_by' => auth()->id(),
            ]);
        });

        static::updated(function (Order $order) {
            if ($order->isDirty('order_status')) {
                $order->statusHistories()->create([
                    'status' => $order->order_status,
                    'remarks' => 'Order status updated to '.str_replace('_', ' ', $order->order_status).'.',
                    'updated_by' => auth()->id(),
                ]);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->items();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function deliverySchedule(): HasOne
    {
        return $this->hasOne(DeliverySchedule::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
