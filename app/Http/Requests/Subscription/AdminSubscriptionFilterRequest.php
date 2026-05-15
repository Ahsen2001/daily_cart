<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class AdminSubscriptionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminUser() ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'frequency' => ['nullable', 'in:daily,weekly,monthly'],
            'status' => ['nullable', 'in:active,paused,cancelled,completed'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
}
