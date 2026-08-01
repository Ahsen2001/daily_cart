<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSlip extends Model
{
    use HasFactory;

    protected $fillable = ['bank_transfer_payment_id', 'uploaded_by', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'uploaded_at'];

    protected function casts(): array { return ['uploaded_at' => 'datetime']; }

    public function bankTransferPayment(): BelongsTo { return $this->belongsTo(BankTransferPayment::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}
