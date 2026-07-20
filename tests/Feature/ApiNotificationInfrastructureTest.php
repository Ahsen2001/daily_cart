<?php

namespace Tests\Feature;

use App\Jobs\SendPublicPromotionPushJob;
use App\Jobs\SendPushNotificationJob;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiNotificationInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_device_tokens_are_registered_refreshed_and_revoked_per_user_device_and_app_role(): void
    {
        $customer = $this->mobileUser('Customer');
        Sanctum::actingAs($customer, ['customer']);

        $this->postJson('/api/v1/notifications/device-tokens', [
            'device_token' => 'customer-token-v1',
            'device_id' => 'installation-123',
            'app_role' => 'customer',
            'platform' => 'android',
            'app_version' => '1.0.0+1',
        ])
            ->assertCreated()
            ->assertJsonPath('device_token.app_role', 'customer')
            ->assertJsonPath('device_token.device_id', 'installation-123');

        $this->patchJson('/api/v1/notifications/device-tokens', [
            'device_token' => 'customer-token-v2',
            'old_device_token' => 'customer-token-v1',
            'device_id' => 'installation-123',
            'app_role' => 'customer',
            'platform' => 'android',
            'app_version' => '1.0.1+2',
        ])
            ->assertOk()
            ->assertJsonPath('device_token.app_version', '1.0.1+2');

        $this->assertDatabaseHas('device_tokens', [
            'token' => 'customer-token-v1',
            'user_id' => $customer->id,
            'app_role' => 'customer',
        ]);
        $this->assertNotNull(
            $customer->deviceTokens()->where('token', 'customer-token-v1')->value('revoked_at')
        );
        $this->assertDatabaseHas('device_tokens', [
            'token' => 'customer-token-v2',
            'user_id' => $customer->id,
            'device_id' => 'installation-123',
            'app_role' => 'customer',
            'revoked_at' => null,
        ]);

        $this->deleteJson('/api/v1/notifications/device-tokens', [
            'device_id' => 'installation-123',
            'device_token' => 'customer-token-v2',
            'app_role' => 'customer',
        ])
            ->assertOk()
            ->assertJsonPath('revoked_count', 1);

        $this->assertNotNull(
            $customer->deviceTokens()->where('token', 'customer-token-v2')->value('revoked_at')
        );
    }

    public function test_a_device_cannot_be_associated_with_another_role_app(): void
    {
        $customer = $this->mobileUser('Customer');
        Sanctum::actingAs($customer, ['customer']);

        $this->postJson('/api/v1/notifications/device-tokens', [
            'device_token' => 'wrong-role-token',
            'device_id' => 'installation-456',
            'app_role' => 'vendor',
            'platform' => 'ios',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('app_role');

        $this->assertDatabaseMissing('device_tokens', ['token' => 'wrong-role-token']);
    }

    public function test_notification_preferences_are_role_scoped_and_persisted(): void
    {
        $rider = $this->mobileUser('Rider');
        Sanctum::actingAs($rider, ['rider']);

        $this->getJson('/api/v1/notifications/preferences')
            ->assertOk()
            ->assertJsonPath('preferences.app_role', 'rider')
            ->assertJsonPath('preferences.push_enabled', true);

        $this->patchJson('/api/v1/notifications/preferences', [
            'app_role' => 'rider',
            'promotions' => false,
            'wallet_updates' => false,
        ])
            ->assertOk()
            ->assertJsonPath('preferences.promotions', false)
            ->assertJsonPath('preferences.wallet_updates', false);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $rider->id,
            'app_role' => 'rider',
            'promotions' => false,
            'wallet_updates' => false,
        ]);
    }

    public function test_database_notifications_are_structured_role_filtered_and_readable(): void
    {
        $vendor = $this->mobileUser('Vendor');
        Sanctum::actingAs($vendor, ['vendor']);

        $notification = Notification::create([
            'user_id' => $vendor->id,
            'title' => 'New order',
            'message' => 'Order DC-100 is ready.',
            'type' => 'new_order',
            'app_role' => 'vendor',
            'data' => ['order_id' => 100, 'status' => 'placed'],
            'deep_link' => '/vendor-order-details/100',
        ]);
        Notification::create([
            'user_id' => $vendor->id,
            'title' => 'Wrong app',
            'message' => 'Must not leak.',
            'type' => 'system',
            'app_role' => 'customer',
        ]);

        $this->getJson('/api/v1/vendor/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'notifications.data')
            ->assertJsonPath('notifications.data.0.id', $notification->id)
            ->assertJsonPath('notifications.data.0.data.order_id', 100)
            ->assertJsonPath('notifications.data.0.deep_link', '/vendor-order-details/100')
            ->assertJsonPath('unread_count', 1);

        $this->patchJson('/api/v1/vendor/notifications/'.$notification->id.'/read')
            ->assertOk()
            ->assertJsonPath('notification.id', $notification->id);

        $this->assertNotNull($notification->refresh()->read_at);
    }

    public function test_private_delivery_uses_a_device_job_and_public_promotions_use_only_a_topic_job(): void
    {
        $customer = $this->mobileUser('Customer');
        $notifications = app(NotificationService::class);

        $notification = $notifications->send(
            $customer,
            'Order update',
            'Your order is on the way.',
            'order_update:42:on_the_way',
            ['database', 'push'],
            ['order_id' => 42, 'status' => 'on_the_way'],
        );

        $this->assertSame('customer', $notification->app_role);
        $this->assertSame('/order-details/42', $notification->deep_link);
        Queue::assertPushed(
            SendPushNotificationJob::class,
            fn (SendPushNotificationJob $job) => $job->notificationId === $notification->id,
        );
        Queue::assertNotPushed(SendPublicPromotionPushJob::class);

        $notifications->sendPublicPromotion(
            'Weekend savings',
            'Public offer available now.',
            ['promotion_id' => 9, 'deep_link' => '/promotion-details/9'],
        );

        Queue::assertPushed(
            SendPublicPromotionPushJob::class,
            fn (SendPublicPromotionPushJob $job) => $job->appRole === 'customer',
        );
    }

    private function mobileUser(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $user->assignRole($role);

        if ($roleName === 'Vendor') {
            $user->vendor()->update(['status' => 'approved']);
        }
        if ($roleName === 'Rider') {
            $user->rider()->update(['verification_status' => 'verified']);
        }

        return $user->refresh();
    }
}
