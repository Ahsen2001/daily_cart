<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PromotionService
{
    private const STOREFRONT_CACHE_PREFIX = 'storefront:promotions:';

    public function create(array $data, User $user, ?int $vendorId = null, ?UploadedFile $banner = null): Promotion
    {
        $data['vendor_id'] = $vendorId;
        $data['created_by'] = $user->id;
        $data['banner_image'] = $banner?->store('promotions', 'public');

        $promotion = Promotion::create($data);
        $this->forgetStorefrontCache();

        return $promotion;
    }

    public function update(Promotion $promotion, array $data, ?UploadedFile $banner = null): Promotion
    {
        if ($banner) {
            $data['banner_image'] = $banner->store('promotions', 'public');
        }

        $promotion->update($data);
        $this->forgetStorefrontCache();

        return $promotion->refresh();
    }

    public function active(): Collection
    {
        return $this->storefront();
    }

    public function storefront(?int $limit = null): Collection
    {
        $cacheKey = self::STOREFRONT_CACHE_PREFIX.($limit ?? 'all');

        return Cache::remember($cacheKey, now()->addMinute(), function () use ($limit) {
            $query = Promotion::query()
                ->visibleOnStorefront()
                ->with(['vendor', 'targetProduct.category', 'targetProduct.images'])
                ->latest();

            if ($limit !== null) {
                $query->limit($limit);
            }

            return $query->get();
        });
    }

    private function forgetStorefrontCache(): void
    {
        Cache::forget(self::STOREFRONT_CACHE_PREFIX.'all');
        Cache::forget(self::STOREFRONT_CACHE_PREFIX.'6');
    }

    public function applicableTo(Product $product): Collection
    {
        return Promotion::query()
            ->visibleOnStorefront()
            ->where(function ($query) use ($product) {
                $query->where(function ($target) use ($product) {
                    $target->where('target_type', 'product')
                        ->where('target_id', $product->id);
                })->orWhere(function ($target) use ($product) {
                    $target->where('target_type', 'category')
                        ->where('target_id', $product->category_id);
                })->orWhere(function ($target) use ($product) {
                    $target->where('target_type', 'vendor')
                        ->where('target_id', $product->vendor_id);
                })->orWhere('target_type', 'global');
            })
            ->latest()
            ->get();
    }

    /** @return array{base_price: float, final_price: float, discount: float, promotion: Promotion|null} */
    public function pricingFor(Product $product, ?ProductVariant $variant = null): array
    {
        $basePrice = round((float) ($variant?->price ?? ($product->discount_price ?: $product->price)), 2);
        $bestPromotion = null;
        $bestDiscount = 0.0;

        foreach ($this->applicableTo($product) as $promotion) {
            $discount = $promotion->discountFor($basePrice);

            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestPromotion = $promotion;
            }
        }

        return [
            'base_price' => $basePrice,
            'final_price' => round($basePrice - $bestDiscount, 2),
            'discount' => $bestDiscount,
            'promotion' => $bestPromotion,
        ];
    }

    /**
     * Count a promotion at most once per visitor session per day. This keeps a
     * refresh from inflating the vendor's view metric.
     */
    public function recordImpressions(Collection $promotions, Request $request): void
    {
        $date = now()->toDateString();
        $viewed = $request->session()->get('promotion_views', []);
        $ids = $promotions->pluck('id')
            ->filter(fn ($id) => ($viewed[$id] ?? null) !== $date)
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        Promotion::query()->whereKey($ids)->increment('views_count');

        foreach ($ids as $id) {
            $viewed[$id] = $date;
        }

        $request->session()->put('promotion_views', $viewed);
    }

    public function recordClick(Product $product, ?int $promotionId): void
    {
        if (! $promotionId) {
            return;
        }

        $promotion = $this->applicableTo($product)->firstWhere('id', $promotionId);

        if ($promotion) {
            $promotion->increment('clicks_count');
        }
    }
}
