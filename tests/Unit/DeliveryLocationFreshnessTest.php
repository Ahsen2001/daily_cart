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

        $live = $freshness->describe(now()->subSeconds(89), true, 'on_the_way');
        $this->assertSame('live', $live['status']);
        $this->assertSame(89, $live['age_seconds']);
        $this->assertFalse($live['is_stale']);

        $recent = $freshness->describe(now()->subSeconds(90), true, 'on_the_way');
        $this->assertSame('recent', $recent['status']);
        $this->assertSame('Last updated 1 minute ago', $recent['message']);
        $this->assertTrue($recent['is_stale']);
        $this->assertFalse($recent['is_outdated']);

        $outdated = $freshness->describe(now()->subSeconds(181), true, 'on_the_way');
        $this->assertSame('outdated', $outdated['status']);
        $this->assertSame('Location may be outdated', $outdated['message']);
        $this->assertTrue($outdated['is_stale']);
        $this->assertTrue($outdated['is_outdated']);
        $this->assertSame('waiting', $freshness->describe(null, true, 'on_the_way')['status']);
        $this->assertSame('completed', $freshness->describe(now(), false, 'delivered')['status']);
    }
}
