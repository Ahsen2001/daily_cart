<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'brand',
        'description',
        'price',
        'discount_price',
        'unit_type',
        'weight',
        'sku',
        'barcode',
        'stock_quantity',
        'expiry_date',
        'image',
        'created_by',
        'base_price',
        'sale_price',
        'unit',
        'status',
        'is_featured',
        'is_subscription_eligible',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'expiry_date' => 'date',
            'is_featured' => 'boolean',
            'is_subscription_eligible' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('storefront:featured-products'));
        static::deleted(fn () => Cache::forget('storefront:featured-products'));

        static::deleting(function (Product $product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->images()->get()->each->delete();
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function productImages(): HasMany
    {
        return $this->images();
    }

    public function getDisplayImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/'.$this->image);
        }

        $galleryImages = $this->relationLoaded('images')
            ? $this->images
            : $this->images()
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->get();

        $galleryImage = $galleryImages
            ->sortBy(fn (ProductImage $image) => sprintf(
                '%d-%010d-%010d',
                $image->is_primary ? 0 : 1,
                $image->sort_order ?? 0,
                $image->id ?? 0
            ))
            ->first(
                fn (ProductImage $image) => $image->image_path && Storage::disk('public')->exists($image->image_path)
            );

        if ($galleryImage) {
            return asset('storage/'.$galleryImage->image_path);
        }

        if ($this->relationLoaded('category') || $this->category_id) {
            return $this->category?->display_image_url ?? asset('images/logo.png');
        }

        return asset('images/logo.png');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productVariants(): HasMany
    {
        return $this->variants();
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeVisibleToCustomers($query)
    {
        return $query->approved()
            ->whereHas('category', fn ($category) => $category->where('status', 'active'))
            ->whereHas('vendor', fn ($vendor) => $vendor->where('status', 'approved'));
    }

    public function averageRating(): float
    {
        return round((float) $this->reviews()->where('status', 'visible')->avg('rating'), 1);
    }

    public function reviewCount(): int
    {
        return $this->reviews()->where('status', 'visible')->count();
    }

    public function brandRelation(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function brand(): BelongsTo
    {
        return $this->brandRelation();
    }
}
