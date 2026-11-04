<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class SubscriptionRunAttempt extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'subscription_run_id', 'attempt_number', 'status', 'error_class',
        'error_message', 'started_at', 'completed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime', 'metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Subscription run attempts are immutable.'));
        static::deleting(fn () => throw new LogicException('Subscription run attempts cannot be deleted.'));
    }

    public function run(): BelongsTo { return $this->belongsTo(SubscriptionRun::class, 'subscription_run_id'); }
}
