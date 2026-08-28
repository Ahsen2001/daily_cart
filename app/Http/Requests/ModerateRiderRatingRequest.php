<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModerateRiderRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('moderate', $this->route('riderRating')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:visible,hidden'],
        ];
    }
}
