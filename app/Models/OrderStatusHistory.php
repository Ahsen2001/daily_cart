<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history';

    protected $fillable = [
        'order_id',
        'status',
        'remarks',
        'updated_by',
    ];

    public function displayCreatedAt(?string $format = null): string
    {
        if (! $this->created_at) {
            return '';
        }

        $timezone = Setting::query()->where('setting_key', 'timezone')->value('setting_value') ?: 'Asia/Colombo';

        return $this->created_at
            ->copy()
            ->timezone($timezone)
            ->format($format ?? 'M d, Y h:i A');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
