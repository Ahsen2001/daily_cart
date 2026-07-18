<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPromotion;
use App\Models\Zone;
use App\Services\DeliveryFeeService;
use App\Services\FinancialPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryPricingController extends Controller
{
    public function zones(): JsonResponse
    {
        return response()->json(['zones' => Zone::query()->where('status', 'active')->orderBy('name')->get([
            'id', 'name', 'district', 'province', 'latitude', 'longitude', 'radius_km', 'estimated_delivery_minutes',
        ])]);
    }

    public function promotions(): JsonResponse
    {
        return response()->json(['promotions' => DeliveryPromotion::query()->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', today()))
            ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', today()))
            ->orderBy('priority')->get()]);
    }

    public function estimate(Request $request, DeliveryFeeService $deliveryFees, FinancialPolicyService $financialPolicy): JsonResponse
    {
        $data = $request->validate([
            'subtotal' => ['required', 'numeric', 'min:0'],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'distance_meters' => ['nullable', 'integer', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
        ]);
        $customer = $request->user()?->customer;
        $estimate = $deliveryFees->estimate((float) $data['subtotal'], $data['district'] ?? null, $data['distance_meters'] ?? null, $customer, $data['province'] ?? null, $data['coupon_code'] ?? null);

        return response()->json([
            'delivery' => $estimate,
            'service_charge' => $financialPolicy->serviceCharge($data['subtotal']),
            'customer_total' => round((float) $data['subtotal'] + $estimate['delivery_fee'] + $financialPolicy->serviceCharge($data['subtotal']), 2),
        ]);
    }
}
