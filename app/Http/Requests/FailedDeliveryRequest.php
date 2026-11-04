<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FailedDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('delivery')) ?? false;
    }

    public function rules(): array
    {
        return [
            'failed_reason_category' => ['required', 'string', Rule::in(array_keys(config('delivery_recovery.reason_categories', [])))],
            'failed_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
