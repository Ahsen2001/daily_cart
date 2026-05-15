<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'spend_amount_per_point',
        'redemption_value_per_point',
        'status',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'redemption_value_per_point' => 'decimal:2',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
