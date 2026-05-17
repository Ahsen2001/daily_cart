<?php

namespace App\Http\Requests;

use App\Services\DeliveryScheduleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->customer !== null;
    }

    public function rules(): array
    {
        return [
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery_distance_meters' => ['nullable', 'integer', 'min:0'],
            'scheduled_delivery_at' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash_on_delivery', 'card', 'bank_transfer', 'wallet'])],
            'coupon_code' => ['nullable', 'string', 'max:255'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $scheduledAt = $this->date('scheduled_delivery_at');

            if (! $scheduledAt) {
                return;
            }

            $schedule = app(DeliveryScheduleService::class);

            if (! $schedule->isAllowed(Carbon::parse($scheduledAt), now())) {
                $validator->errors()->add('scheduled_delivery_at', DeliveryScheduleService::ERROR_MESSAGE);
            }
        });
    }
}
