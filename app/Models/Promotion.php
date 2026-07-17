<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'title',
        'description',
        'promotion_type',
        'target_type',
        'target_id',
        'discount_type',
        'discount_value',
        'banner_image',
        'starts_at',
        'ends_at',
        'status',
        'created_by',
        'views_count',
        'clicks_count',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeVisibleOnStorefront($query)
    {
        return $query->active()
            ->where(function ($vendorQuery) {
                $vendorQuery->whereNull('vendor_id')
                    ->orWhereHas('vendor', fn ($vendor) => $vendor->where('status', 'approved'));
            })
            ->where(function ($targetQuery) {
                $targetQuery->where('target_type', '!=', 'product')
                    ->orWhereHas('targetProduct', function ($product) {
                        $product->visibleToCustomers()
                            ->whereHas('vendor', fn ($vendor) => $vendor->where('status', 'approved'));
                    });
            });
    }

    public function discountFor(float $price): float
    {
        $discount = $this->discount_type === 'percentage'
            ? $price * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return round(min(max($discount, 0), $price), 2);
    }

    public function priceFor(float $price): float
    {
        return round(max($price - $this->discountFor($price), 0), 2);
    }
}
