<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StoreBanner extends Model
{
    protected $fillable = ['vendor_profile_id', 'title', 'image_path', 'link_url', 'sort_order', 'is_active', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? asset('storage/'.$this->image_path)
            : asset('images/logo.png');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($dates) => $dates->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($dates) => $dates->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
