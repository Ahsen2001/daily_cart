<?php

namespace App\Http\Requests;

use App\Models\Refund;
use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('createForOrder', [Refund::class, $this->route('order')]) ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }
}
