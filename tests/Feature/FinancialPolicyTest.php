<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Vendor;
use App\Services\FinancialPolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinancialPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_charge_honours_percentage_fixed_minimum_and_cap(): void
    {
        Setting::putMany([
            'service_charge_rate_percent' => '2',
            'service_charge_flat_amount' => '10',
            'service_charge_minimum' => '25',
            'service_charge_maximum' => '50',
            'delivery_promotion_discount_percent' => '20',
            'delivery_promotion_minimum_subtotal' => '500',
        ]);

        $policy = app(FinancialPolicyService::class);

        $this->assertSame(25.0, $policy->serviceCharge(100));
        $this->assertSame(50.0, $policy->serviceCharge(5000));
        $this->assertSame(100.0, $policy->discountedDeliveryFee(100, 400));
        $this->assertSame(80.0, $policy->discountedDeliveryFee(100, 500));
    }

    public function test_rider_payout_and_vendor_commission_are_settings_driven(): void
    {
        Setting::putMany([
            'rider_payout_base' => '350',
            'rider_payout_per_km' => '20',
            'rider_peak_bonus' => '50',
            'rider_peak_start_hour' => '17',
            'rider_peak_end_hour' => '21',
            'default_vendor_commission_rate' => '10',
        ]);
        $vendor = new Vendor(['commission_rate' => 0]);
        $delivery = new Delivery(['delivered_at' => Carbon::parse('2026-07-18 18:00:00')]);
        $delivery->setRelation('order', new Order(['delivery_distance_meters' => 2000]));
        $policy = app(FinancialPolicyService::class);

        $this->assertSame(440.0, $policy->riderPayout($delivery));
        $this->assertSame(900.0, $policy->vendorPayoutForBase($vendor, 1000));
        $order = new Order(['subtotal' => 1000, 'discount_amount' => 0, 'loyalty_discount_amount' => 0]);
        $order->setRelation('vendor', $vendor);
        $this->assertSame(900.0, $policy->vendorPayout($order));
    }
}
