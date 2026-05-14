<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EarningFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPrimaryRole('Vendor', 'Rider') ?? false;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
}
