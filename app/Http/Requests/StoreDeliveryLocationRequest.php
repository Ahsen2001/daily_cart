<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rider !== null;
    }

    public function rules(): array
    {
        return [
            'tracking_token' => ['required', 'string', 'size:64'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'between:0,10000'],
            'altitude' => ['nullable', 'numeric', 'between:-500,20000'],
            'speed' => ['nullable', 'numeric', 'between:0,200'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
