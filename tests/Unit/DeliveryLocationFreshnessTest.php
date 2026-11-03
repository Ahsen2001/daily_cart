<?php

namespace Tests\Unit;

use App\Services\DeliveryLocationFreshness;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeliveryLocationFreshnessTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_describes_live_recent_outdated_waiting_and_completed_locations(): void
    {
        Carbon::setTestNow('2026-11-03 12:00:00');
        config([
            'delivery_tracking.fresh_seconds' => 90,
            'delivery_tracking.outdated_seconds' => 180,
        ]);
        $freshness = app(DeliveryLocationFreshness::class);

        $this->assertSame('live', $freshness->describe(now()->subSeconds(89), true, 'on_the_way')['status']);
        $this->assertSame('recent', $freshness->describe(now()->subSeconds(90), true, 'on_the_way')['status']);
        $this->assertSame('outdated', $freshness->describe(now()->subSeconds(181), true, 'on_the_way')['status']);
        $this->assertSame('waiting', $freshness->describe(null, true, 'on_the_way')['status']);
        $this->assertSame('completed', $freshness->describe(now(), false, 'delivered')['status']);
    }
}
