<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubscriptionRun extends Model
{
    protected $fillable = [
        'subscription_id', 'scheduled_date', 'run_key', 'status', 'attempt_count',
        'max_attempts', 'next_retry_at', 'first_dispatched_at', 'started_at',
        'completed_at', 'escalated_at', 'failure_reason', 'last_error_class', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date', 'attempt_count' => 'integer', 'max_attempts' => 'integer',
            'next_retry_at' => 'datetime', 'first_dispatched_at' => 'datetime',
            'started_at' => 'datetime', 'completed_at' => 'datetime',
            'escalated_at' => 'datetime', 'metadata' => 'array',
        ];
    }

    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
    public function attempts(): HasMany { return $this->hasMany(SubscriptionRunAttempt::class); }
    public function order(): HasOne { return $this->hasOne(Order::class); }
}
