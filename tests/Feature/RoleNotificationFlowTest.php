<?php

namespace Tests\Feature;

use App\Mail\GenericNotificationMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RoleNotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_submission_notifies_admins_and_review_notifies_the_vendor(): void
    {
        Mail::fake();

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
        Mail::assertQueued(GenericNotificationMail::class, fn (GenericNotificationMail $mail) => $mail->title === 'Product approval required'
            && $mail->hasTo($admin->email));
        Mail::assertQueued(GenericNotificationMail::class, fn (GenericNotificationMail $mail) => $mail->title === 'Product approval required'
            && $mail->hasTo($superAdmin->email));

        $this->actingAs($admin)
            ->patch(route('admin.products.approve', $product))
            ->assertSessionHas('status');

        $this->assertSame('approved', $product->refresh()->status);
        $this->assertDatabaseHas('notifications', ['user_id' => $vendorUser->id, 'title' => 'Product approved']);
        Mail::assertQueued(GenericNotificationMail::class, fn (GenericNotificationMail $mail) => $mail->title === 'Product approved'
            && $mail->hasTo($vendorUser->email));
        $this->assertSame($vendor->id, $product->vendor_id);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->assignRole($role);

        return $user;
    }
}
