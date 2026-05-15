<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPrimaryRole('Vendor', 'Admin', 'Super Admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'promotion_type' => ['required', 'in:flash_sale,seasonal_offer,featured_offer,clearance_sale'],
            'target_type' => ['required', 'in:product,category,vendor,global'],
            'target_id' => ['nullable', 'integer'],
            'discount_type' => ['required', 'in:fixed_amount,percentage'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['required', 'in:active,inactive,expired'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
        ];
    }
}
