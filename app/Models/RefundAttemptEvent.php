<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class RefundAttemptEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'refund_id', 'payment_id', 'payment_gateway_transaction_id', 'attempt_uuid',
        'event_type', 'gateway', 'amount', 'currency', 'gateway_refund_id',
        'http_status', 'error_code', 'message', 'request_payload', 'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Refund attempt events are immutable.'));
        static::deleting(fn () => throw new LogicException('Refund attempt events cannot be deleted.'));
    }

    public function refund(): BelongsTo { return $this->belongsTo(Refund::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function gatewayTransaction(): BelongsTo { return $this->belongsTo(PaymentGatewayTransaction::class, 'payment_gateway_transaction_id'); }
}
