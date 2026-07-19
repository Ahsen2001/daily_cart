<?php

namespace Tests\Feature;

use App\Models\DeliveryHoliday;
use App\Models\DeliveryPricingRule;
use App\Models\DeliveryPromotion;
use App\Models\FreeDeliveryRule;
use App\Services\DeliveryFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryPricingEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_district_rule_returns_the_fee_eta_and_free_delivery_eligibility(): void
    {
        DeliveryPricingRule::query()->create([
            'scope' => 'district',
            'district' => 'Colombo',
            'base_fee' => 100,
            'per_km_fee' => 10,
            'minimum_order' => 500,
            'free_delivery_threshold' => 1000,
            'maximum_distance_km' => 10,
            'estimated_delivery_minutes' => 45,
            'priority' => 10,
            'status' => 'active',
        ]);

        $service = app(DeliveryFeeService::class);
        $standard = $service->estimate(900, 'Colombo', 2000);
        $free = $service->estimate(1000, 'Colombo', 2000);

        $this->assertSame(120.0, $standard['delivery_fee']);
        $this->assertSame(45, $standard['estimated_delivery_minutes']);
        $this->assertFalse($standard['free_delivery_eligible']);
        $this->assertSame('district', $standard['rule_scope']);
        $this->assertSame(0.0, $free['delivery_fee']);
        $this->assertTrue($free['free_delivery_eligible']);
    }

    public function test_active_delivery_policies_are_applied_to_the_same_checkout_estimate(): void
    {
        DeliveryPricingRule::query()->create([
            'scope' => 'default', 'base_fee' => 200, 'per_km_fee' => 0, 'minimum_order' => 0,
            'priority' => 100, 'status' => 'active',
        ]);
        DeliveryPromotion::query()->create([
            'name' => 'New Year', 'type' => 'percentage_discount', 'discount_percent' => 50,
            'minimum_order' => 500, 'priority' => 1, 'status' => 'active',
        ]);
        DeliveryHoliday::query()->create([
            'name' => 'Festival', 'extra_charge' => 20, 'starts_on' => today(), 'ends_on' => today(), 'status' => 'active',
        ]);

        $discounted = app(DeliveryFeeService::class)->estimate(600);
        $this->assertSame(120.0, $discounted['delivery_fee']);

        FreeDeliveryRule::query()->create([
            'name' => 'Coupon Free Delivery', 'condition_type' => 'coupon', 'coupon_code' => 'FREESHIP',
            'minimum_order' => 0, 'priority' => 1, 'status' => 'active',
        ]);
        $free = app(DeliveryFeeService::class)->estimate(600, null, null, null, null, 'FREESHIP');
        $this->assertSame(0.0, $free['delivery_fee']);
        $this->assertTrue($free['free_delivery_eligible']);
    }
}
