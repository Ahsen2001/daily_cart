<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPrimaryRole('Customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'preferred_delivery_time' => ['required', 'date_format:H:i'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'payment_method' => ['required', 'in:cash_on_delivery,card,bank_transfer,wallet'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
