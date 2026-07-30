<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class VendorProfile extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'vendor_id', 'slug', 'description', 'logo_path', 'cover_image_path', 'opening_hours',
        'delivery_estimate', 'minimum_order', 'contact_phone', 'contact_email', 'is_featured',
        'is_enabled', 'profile_status', 'approved_at', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'minimum_order' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_enabled' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function banners(): HasMany
    {
        return $this->hasMany(StoreBanner::class)->orderBy('sort_order');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'store_followers')->withTimestamps();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('profile_status', 'approved')
            ->where('is_enabled', true)
            ->whereHas('vendor', fn ($vendor) => $vendor->where('status', 'approved'));
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->storedAssetUrl($this->logo_path);
    }

    public function getCoverImageUrlAttribute(): string
    {
        return $this->storedAssetUrl($this->cover_image_path);
    }

    private function storedAssetUrl(?string $path): string
    {
        if ($path && filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return asset('images/logo.png');
    }
}
