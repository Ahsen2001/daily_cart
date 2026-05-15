<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoyaltySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminUser() ?? false;
    }

    public function rules(): array
    {
        return [
            'spend_amount_per_point' => ['required', 'integer', 'min:1'],
            'redemption_value_per_point' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
