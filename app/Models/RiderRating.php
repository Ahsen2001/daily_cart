<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderRating extends Model
{
    use HasFactory;

    public const TAGS = [
        'on_time',
        'friendly',
        'careful_handling',
        'good_communication',
        'late_delivery',
        'poor_communication',
    ];

    protected $fillable = [
        'order_id',
        'delivery_id',
        'customer_id',
        'rider_id',
        'rating',
        'tags',
        'comment',
        'status',
        'report_reason',
        'reported_at',
        'moderated_by',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'tags' => 'array',
            'reported_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
