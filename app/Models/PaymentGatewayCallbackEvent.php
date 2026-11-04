<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PaymentGatewayCallbackEvent extends Model
{
    protected $fillable = [
        'payment_gateway_transaction_id',
        'payment_id',
        'gateway',
        'event_key',
        'gateway_order_id',
        'gateway_payment_id',
        'merchant_id',
        'status_code',
        'normalized_status',
        'amount',
        'currency',
        'signature_valid',
        'identity_valid',
        'processing_result',
        'rejection_reason',
        'payload',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'signature_valid' => 'boolean',
            'identity_valid' => 'boolean',
            'payload' => 'array',
            'received_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Gateway callback events are immutable.'));
        static::deleting(fn () => throw new LogicException('Gateway callback events are immutable.'));
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentGatewayTransaction::class, 'payment_gateway_transaction_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
