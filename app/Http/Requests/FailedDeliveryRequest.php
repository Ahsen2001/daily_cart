<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FailedDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('delivery')) ?? false;
    }

    public function rules(): array
    {
        return [
            'failed_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
