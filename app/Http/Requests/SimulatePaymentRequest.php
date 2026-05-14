<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimulatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('process', $this->route('payment')) ?? false;
    }

    public function rules(): array
    {
        return [
            'result' => ['required', 'in:success,failed'],
        ];
    }
}
