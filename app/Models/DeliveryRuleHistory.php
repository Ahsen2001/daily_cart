<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRuleHistory extends Model
{
    use HasFactory;

    protected $fillable = ['delivery_pricing_rule_id', 'user_id', 'action', 'changes'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function rule(): BelongsTo { return $this->belongsTo(DeliveryPricingRule::class, 'delivery_pricing_rule_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
