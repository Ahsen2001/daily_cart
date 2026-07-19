<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DeliveryProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'proof_image',
        'customer_signature',
        'note',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function getProofImageUrlAttribute(): ?string
    {
        $path = $this->proof_image;

        if (! is_string($path) || $path === '' || $path === '0' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        // Keep the proof on the current host. APP_URL may point to a tunnel while
        // a rider is working on localhost.
        return '/storage/'.implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
