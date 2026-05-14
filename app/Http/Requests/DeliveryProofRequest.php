<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('delivery')) ?? false;
    }

    public function rules(): array
    {
        return [
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'customer_signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
