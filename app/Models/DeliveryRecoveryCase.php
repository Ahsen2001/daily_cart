<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryRecoveryCase extends Model
{
    protected $fillable = [
        'delivery_id', 'order_id', 'failure_sequence', 'reason_category', 'reason_details',
        'status', 'resolution', 'refund_decision', 'refund_id', 'failed_by',
        'assigned_admin_id', 'escalation_due_at', 'escalated_at', 'return_started_at',
        'return_completed_at', 'resolved_at', 'resolved_by', 'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'escalation_due_at' => 'datetime', 'escalated_at' => 'datetime',
            'return_started_at' => 'datetime', 'return_completed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function delivery(): BelongsTo { return $this->belongsTo(Delivery::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function refund(): BelongsTo { return $this->belongsTo(Refund::class); }
    public function failedBy(): BelongsTo { return $this->belongsTo(User::class, 'failed_by'); }
    public function assignedAdmin(): BelongsTo { return $this->belongsTo(User::class, 'assigned_admin_id'); }
    public function resolvedBy(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }
    public function events(): HasMany { return $this->hasMany(DeliveryRecoveryEvent::class); }

    public function isResolved(): bool { return $this->status === 'resolved'; }
}
