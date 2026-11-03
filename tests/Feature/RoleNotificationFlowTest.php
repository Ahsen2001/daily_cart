<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Notification;
use App\Models\NotificationDeliveryLog;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleNotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_submission_notifies_admins_and_review_notifies_the_vendor(): void
    {
        $admin = $this->userWithRole('Admin');
        $superAdmin = $this->userWithRole('Super Admin');
        $vendorUser = $this->userWithRole('Vendor');
        $vendor = $vendorUser->vendor()->firstOrFail();
        $vendor->update([
            'store_name' => 'Notification Store',
            'phone' => '0771234567',
            'address' => '1 Market Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $category = Category::query()->create([
            'name' => 'Notification Category',
            'slug' => 'notification-category',
            'status' => 'active',
        ]);

        $this->actingAs($vendorUser)
            ->post(route('vendor.products.store'), [
                'category_id' => $category->id,
                'name' => 'Approval Notification Product',
                'price' => 450,
                'unit_type' => 'item',
                'stock_quantity' => 5,
            ])
            ->assertRedirect();

        $product = Product::query()->sole();

        $this->assertSame('pending', $product->status);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'type' => 'product_submitted_for_approval']);
        $this->assertDatabaseHas('notifications', ['user_id' => $superAdmin->id, 'type' => 'product_submitted_for_approval']);
        $this->assertQueuedEmailDelivery($admin, 'Product approval required');
        $this->assertQueuedEmailDelivery($superAdmin, 'Product approval required');

        $this->actingAs($admin)
            ->patch(route('admin.products.approve', $product))
            ->assertSessionHas('status');

        $this->assertSame('approved', $product->refresh()->status);
        $this->assertDatabaseHas('notifications', ['user_id' => $vendorUser->id, 'title' => 'Product approved']);
        $this->assertQueuedEmailDelivery($vendorUser, 'Product approved');
        $this->assertSame($vendor->id, $product->vendor_id);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->assignRole($role);

        return $user;
    }

    private function assertQueuedEmailDelivery(User $user, string $title): void
    {
        $notification = Notification::query()
            ->whereBelongsTo($user)
            ->where('title', $title)
            ->sole();

        $this->assertDatabaseHas('notification_delivery_logs', [
            'notification_id' => $notification->id,
            'user_id' => $user->id,
            'event_id' => $notification->event_id,
            'channel' => 'email',
        ]);
        $this->assertContains(
            NotificationDeliveryLog::query()
                ->where('notification_id', $notification->id)
                ->where('channel', 'email')
                ->sole()
                ->status,
            ['queued', 'sent'],
        );
    }
}
