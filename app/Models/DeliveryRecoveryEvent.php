<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DeliveryRecoveryEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'delivery_recovery_case_id', 'actor_id', 'event_type', 'from_status',
        'to_status', 'note', 'metadata',
    ];

    protected function casts(): array { return ['metadata' => 'array']; }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Delivery recovery events are immutable.'));
        static::deleting(fn () => throw new LogicException('Delivery recovery events cannot be deleted.'));
    }

    public function recoveryCase(): BelongsTo { return $this->belongsTo(DeliveryRecoveryCase::class, 'delivery_recovery_case_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }
}
