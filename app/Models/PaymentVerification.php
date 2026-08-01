<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVerification extends Model
{
    protected $fillable = ['bank_transfer_payment_id', 'payment_slip_id', 'verified_by', 'action', 'status', 'reason', 'verified_at'];
    protected function casts(): array { return ['verified_at' => 'datetime']; }
    public function bankTransferPayment(): BelongsTo { return $this->belongsTo(BankTransferPayment::class); }
    public function slip(): BelongsTo { return $this->belongsTo(PaymentSlip::class, 'payment_slip_id'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
}
