<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPrimaryRole('Customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'preferred_delivery_time' => ['required', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'payment_method' => ['required', 'in:cash_on_delivery,card,bank_transfer,wallet'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
