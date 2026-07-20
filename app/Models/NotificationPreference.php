<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'app_role',
        'push_enabled',
        'order_updates',
        'delivery_updates',
        'wallet_updates',
        'support_updates',
        'promotions',
    ];

    protected function casts(): array
    {
        return [
            'push_enabled' => 'boolean',
            'order_updates' => 'boolean',
            'delivery_updates' => 'boolean',
            'wallet_updates' => 'boolean',
            'support_updates' => 'boolean',
            'promotions' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allows(string $notificationType): bool
    {
        if (! $this->push_enabled) {
            return false;
        }

        $normalized = strtolower($notificationType);

        return match (true) {
            str_contains($normalized, 'promotion'),
            str_contains($normalized, 'coupon') => $this->promotions,
            str_contains($normalized, 'delivery'),
            str_contains($normalized, 'rider') => $this->delivery_updates,
            str_contains($normalized, 'wallet'),
            str_contains($normalized, 'payment'),
            str_contains($normalized, 'payout'),
            str_contains($normalized, 'refund') => $this->wallet_updates,
            str_contains($normalized, 'support'),
            str_contains($normalized, 'ticket') => $this->support_updates,
            default => $this->order_updates,
        };
    }
}
