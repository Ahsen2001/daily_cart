<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankTransferPayment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending_upload', 'slip_uploaded', 'pending_verification', 'verified',
        'rejected', 'refunded', 'cancelled',
    ];

    protected $fillable = [
        'payment_id', 'order_id', 'reference_number', 'bank_name', 'account_holder',
        'account_number', 'branch', 'expected_amount', 'currency', 'status',
        'last_rejection_reason', 'submitted_at', 'verified_at', 'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function slips(): HasMany { return $this->hasMany(PaymentSlip::class); }
    public function verifications(): HasMany { return $this->hasMany(PaymentVerification::class); }
    public function logs(): HasMany { return $this->hasMany(FinancialVerificationLog::class); }
    public function isVerified(): bool { return $this->status === 'verified'; }
}
