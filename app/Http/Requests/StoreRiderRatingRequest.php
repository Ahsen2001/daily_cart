<?php

namespace App\Http\Requests;

use App\Models\RiderRating;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRiderRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rateOrder', [RiderRating::class, $this->route('order')]) ?? false;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'tags' => ['nullable', 'array', 'max:6'],
            'tags.*' => ['string', Rule::in(RiderRating::TAGS)],
        ];
    }
}
