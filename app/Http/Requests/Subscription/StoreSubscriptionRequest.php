<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('subscription_item')) {
            return;
        }

        [$productId, $variantId] = array_pad(explode(':', (string) $this->input('subscription_item'), 2), 2, null);

        $this->merge([
            'product_id' => $productId,
            'product_variant_id' => $variantId && $variantId !== 'base' ? $variantId : null,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasPrimaryRole('Customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'subscription_item' => ['required', 'string'],
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
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
