<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class PromotionService
{
    public function create(array $data, User $user, ?int $vendorId = null, ?UploadedFile $banner = null): Promotion
    {
        $data['vendor_id'] = $vendorId;
        $data['created_by'] = $user->id;
        $data['banner_image'] = $banner?->store('promotions', 'public');

        return Promotion::create($data);
    }

    public function update(Promotion $promotion, array $data, ?UploadedFile $banner = null): Promotion
    {
        if ($banner) {
            $data['banner_image'] = $banner->store('promotions', 'public');
        }

        $promotion->update($data);

        return $promotion->refresh();
    }

    public function active(): Collection
    {
        return $this->storefront();
    }

    public function storefront(?int $limit = null): Collection
    {
        $query = Promotion::query()
            ->visibleOnStorefront()
            ->with(['vendor', 'targetProduct.category', 'targetProduct.images'])
            ->latest();

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
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
}
