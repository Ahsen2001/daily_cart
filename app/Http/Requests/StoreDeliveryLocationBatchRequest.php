<?php

namespace App\Http\Requests;

use App\Models\Delivery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeliveryLocationBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rider !== null;
    }

    public function rules(): array
    {
        /** @var Delivery $delivery */
        $delivery = $this->route('delivery');

        return [
            'tracking_token' => ['required', 'string', 'size:64'],
            'points' => ['required', 'array', 'min:1', 'max:50'],
            'points.*.point_uuid' => ['required', 'uuid'],
            'points.*.delivery_id' => ['required', 'integer', Rule::in([(int) $delivery->getKey()])],
            'points.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'points.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'points.*.accuracy_meters' => ['required', 'numeric', 'gt:0', 'max:100'],
            'points.*.heading' => ['present', 'nullable', 'numeric', 'between:0,360'],
            'points.*.speed' => ['present', 'nullable', 'numeric', 'between:0,70'],
            'points.*.recorded_at' => ['required', 'date'],
            'points.*.sequence_number' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $points = $this->input('points');
        if (is_array($points) && ! array_is_list($points)) {
            $this->merge(['points' => [$points]]);

            return;
        }

        if ($points === null && $this->has('point_uuid')) {
            $this->merge([
                'points' => [[
                    'point_uuid' => $this->input('point_uuid'),
                    'delivery_id' => $this->input('delivery_id'),
                    'latitude' => $this->input('latitude'),
                    'longitude' => $this->input('longitude'),
                    'accuracy_meters' => $this->input('accuracy_meters'),
                    'heading' => $this->input('heading'),
                    'speed' => $this->input('speed'),
                    'recorded_at' => $this->input('recorded_at'),
                    'sequence_number' => $this->input('sequence_number'),
                ]],
            ]);
        }
    }
}
