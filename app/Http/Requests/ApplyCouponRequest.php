<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->customer !== null;
    }

    public function rules(): array
    {
        return [
            'coupon_code' => ['nullable', 'string', 'max:255'],
        ];
    }
}
