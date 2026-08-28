<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRiderRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('report', $this->route('riderRating')) ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
