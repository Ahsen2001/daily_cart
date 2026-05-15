<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\User;
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

    public function active()
    {
        return Promotion::active()->latest()->get();
    }
}
