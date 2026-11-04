<?php

namespace Tests\Feature;

use App\Jobs\SubscriptionOrderJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\SubscriptionRun;
use App\Models\User;
use App\Services\QueueHealthMonitor;
use App\Services\ReliableRecurringOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SubscriptionRetryReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_subscription_date_creates_and_dispatches_only_one_unique_run(): void
    {
        Bus::fake();
        $subscription = $this->subscription(false);
        $service = app(ReliableRecurringOrderService::class);

        $this->assertSame(1, $service->dispatchDueSubscriptions());
        $this->assertSame(0, $service->dispatchDueSubscriptions());
        $this->assertDatabaseCount('subscription_runs', 1);
        $run = SubscriptionRun::firstOrFail();
        $this->assertSame(hash('sha256', $subscription->id.'|'.$subscription->next_delivery_date->toDateString()), $run->run_key);
        Bus::assertDispatchedTimes(SubscriptionOrderJob::class, 1);
    }

    public function test_failures_back_off_then_exhaust_without_daily_redispatch(): void
    {
        Bus::fake();
        $subscription = $this->subscription(false);
        $this->roleUser('Admin');
        $run = SubscriptionRun::create([
            'subscription_id' => $subscription->id,
            'scheduled_date' => $subscription->next_delivery_date,
            'run_key' => hash('sha256', $subscription->id.'|'.$subscription->next_delivery_date->toDateString()),
            'status' => 'queued', 'max_attempts' => 2, 'first_dispatched_at' => now(),
        ]);
        $service = app(ReliableRecurringOrderService::class);

        $this->assertNull($service->processRun($run));
        $run->refresh();
        $this->assertSame('retry_scheduled', $run->status);
        $this->assertSame(1, $run->attempt_count);
        $this->assertTrue($run->next_retry_at->isFuture());
        $this->assertSame($subscription->next_delivery_date->toDateString(), $subscription->refresh()->next_delivery_date->toDateString());

        $this->assertNull($service->processRun($run));
        $run->refresh();
        $this->assertSame('exhausted', $run->status);
        $this->assertSame(2, $run->attempt_count);
        $this->assertNotNull($run->escalated_at);
        $this->assertCount(2, $run->attempts);
        $this->assertSame(0, $service->dispatchDueSubscriptions());
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('notifications', ['type' => 'subscription_run_escalated']);

        $service->retryExhausted($run);
        $this->assertSame('queued', $run->refresh()->status);
        $this->assertSame(3, $run->max_attempts);
        Bus::assertDispatched(SubscriptionOrderJob::class, fn ($job) => $job->subscriptionRunId === $run->id);
    }

    public function test_successful_run_creates_one_order_and_advances_schedule_once(): void
    {
        Bus::fake();
        $subscription = $this->subscription(true);
        $date = $subscription->next_delivery_date->toDateString();
        $run = SubscriptionRun::create([
            'subscription_id' => $subscription->id, 'scheduled_date' => $date,
            'run_key' => hash('sha256', $subscription->id.'|'.$date),
            'status' => 'queued', 'max_attempts' => 3, 'first_dispatched_at' => now(),
        ]);
        $service = app(ReliableRecurringOrderService::class);

        $order = $service->processRun($run);
        $this->assertNotNull($order);
        $this->assertSame($run->id, $order->subscription_run_id);
        $this->assertSame('succeeded', $run->refresh()->status);
        $this->assertSame(1, $run->attempt_count);
        $this->assertSame(now()->addDay()->toDateString(), $subscription->refresh()->next_delivery_date->toDateString());

        $sameOrder = $service->generate($subscription->refresh(), $run->refresh());
        $this->assertSame($order->id, $sameOrder->id);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_queue_monitor_reports_stale_subscription_work(): void
    {
        $subscription = $this->subscription(false);
        $run = SubscriptionRun::create([
            'subscription_id' => $subscription->id, 'scheduled_date' => $subscription->next_delivery_date,
            'run_key' => hash('sha256', $subscription->id.'|monitor'), 'status' => 'running',
            'attempt_count' => 1, 'max_attempts' => 3, 'first_dispatched_at' => now()->subHour(),
            'started_at' => now()->subHour(),
        ]);
        SubscriptionRun::query()->whereKey($run->id)->update(['updated_at' => now()->subHour()]);

        $metrics = app(QueueHealthMonitor::class)->inspect(false);
        $this->assertSame(1, $metrics['stale_subscription_runs']);
        $this->assertFalse($metrics['healthy']);
    }

    private function subscription(bool $eligible): Subscription
    {
        $customer = $this->roleUser('Customer')->customer;
        $vendorUser = $this->roleUser('Vendor');
        $vendor = $vendorUser->vendor;
        $vendor->update(['store_name' => 'Subscription Vendor', 'address' => '1 Vendor Street', 'status' => 'approved']);
        $category = Category::create(['name' => 'Recurring', 'slug' => 'recurring-'.uniqid(), 'status' => 'active']);
        $product = Product::create([
            'vendor_id' => $vendor->id, 'category_id' => $category->id,
            'name' => 'Recurring Product', 'slug' => 'recurring-product-'.uniqid(),
            'base_price' => 100, 'price' => 100, 'unit' => 'item', 'unit_type' => 'item',
            'stock_quantity' => 10, 'status' => 'approved', 'is_subscription_eligible' => $eligible,
        ]);

        return Subscription::create([
            'customer_id' => $customer->id, 'product_id' => $product->id,
            'vendor_id' => $vendor->id, 'frequency' => 'daily', 'quantity' => 1,
            'unit_price' => 100, 'total_amount' => 100, 'delivery_address' => '479 Test Road',
            'preferred_delivery_time' => '10:00:00', 'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(), 'next_delivery_date' => now()->toDateString(),
            'payment_method' => 'cash_on_delivery', 'plan_name' => 'Daily product',
            'price' => 100, 'currency' => 'LKR', 'starts_at' => now(), 'ends_at' => now()->addMonth(),
            'status' => 'active',
        ]);
    }

    private function roleUser(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => '07'.random_int(10000000, 99999999)]);
        $user->assignRole($role);
        return $user->refresh();
    }
}
