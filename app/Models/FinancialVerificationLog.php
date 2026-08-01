<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialVerificationLog extends Model
{
    protected $fillable = ['bank_transfer_payment_id', 'payment_id', 'order_id', 'actor_id', 'action', 'from_status', 'to_status', 'notes', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function bankTransferPayment(): BelongsTo { return $this->belongsTo(BankTransferPayment::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }
}
