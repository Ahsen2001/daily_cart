<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminUser() ?? false;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'title' => ['required', 'string', 'max:255'],
            'image' => [$this->route('advertisement') ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'link_type' => ['required', 'in:product,category,vendor,url'],
            'link_id' => ['nullable', 'integer'],
            'target_url' => ['nullable', 'url', 'max:255'],
            'position' => ['required', 'in:homepage_slider,homepage_banner,category_banner,sidebar,product_page'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['required', 'in:active,inactive,expired'],
        ];
    }
}
