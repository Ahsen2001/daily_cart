<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\DeliveryFeeService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique(User::class, 'phone')->ignore($this->user()->id)],
        ];

        $hasLocationProfile = $this->user()->customer || $this->user()->vendor || $this->user()->rider;

        if (! $hasLocationProfile) {
            return $rules;
        }

        $configuredDistricts = app(DeliveryFeeService::class)->configuredDistricts()->all();
        $districtRules = ['required', 'string', 'max:255'];

        if ($configuredDistricts !== []) {
            $districtRules[] = Rule::in($configuredDistricts);
        }

        $rules['phone'] = ['required', 'string', 'max:30', Rule::unique(User::class, 'phone')->ignore($this->user()->id)];
        $rules += [
            'city' => ['required', 'string', 'max:255'],
            'district' => $districtRules,
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->user()->customer) {
            $rules += [
                'address_line_1' => ['required', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'postal_code' => ['nullable', 'string', 'max:30'],
            ];
        } else {
            $rules['address'] = ['required', 'string', 'max:1000'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'district.in' => 'Select a district supported by the active Delivery Fees Configuration.',
        ];
    }
}
