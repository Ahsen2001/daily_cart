<?php

namespace Tests\Feature;

use App\Models\DeliveryPricingRule;
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
}
