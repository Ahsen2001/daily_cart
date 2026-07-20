<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\Rider;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\DeliveryFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_customer_can_update_the_default_address_using_a_configured_delivery_district(): void
    {
        DeliveryFee::query()->create([
            'district' => 'Kandy',
            'base_fee' => 200,
            'per_km_fee' => 15,
            'minimum_order' => 500,
            'free_delivery_limit' => 5000,
            'status' => 'active',
        ]);
        $role = Role::findOrCreate('Customer', 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => '0771000001']);
        $user->assignRole($role);
        $customer = $user->customer()->firstOrFail();
        $customer->update([
            'first_name' => 'Profile',
            'phone' => $user->phone,
            'status' => 'active',
        ]);
        $address = $customer->addresses()->create([
            'label' => 'Home',
            'recipient_name' => $user->name,
            'phone' => $user->phone,
            'address_line_1' => 'Old Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '0771000099',
                'address_line_1' => '12 Lake Road',
                'address_line_2' => 'Near the market',
                'city' => 'Kandy',
                'district' => 'Kandy',
                'province' => 'Central',
                'postal_code' => '20000',
                'latitude' => '7.2906000',
                'longitude' => '80.6337000',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $address->refresh();

        $this->assertSame('12 Lake Road', $address->address_line_1);
        $this->assertSame('Kandy', $address->district);
        $this->assertSame('0771000099', $customer->refresh()->phone);
        $this->assertSame(200.0, app(DeliveryFeeService::class)->calculate(1000, null, null, 1, $customer));
    }

    public function test_profile_rejects_a_district_outside_the_active_delivery_fee_configuration(): void
    {
        DeliveryFee::query()->create([
            'district' => 'Colombo',
            'base_fee' => 100,
            'per_km_fee' => 10,
            'minimum_order' => 0,
            'status' => 'active',
        ]);
        $role = Role::findOrCreate('Customer', 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => '0772000001']);
        $user->assignRole($role);
        $user->customer()->firstOrFail()->update([
            'first_name' => 'Profile',
            'phone' => $user->phone,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address_line_1' => '1 Main Street',
                'city' => 'Galle',
                'district' => 'Galle',
                'province' => 'Southern',
            ])
            ->assertSessionHasErrors('district');

        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_vendor_and_rider_profile_locations_are_synchronized(): void
    {
        DeliveryFee::query()->create([
            'district' => 'Kandy',
            'base_fee' => 200,
            'per_km_fee' => 15,
            'minimum_order' => 0,
            'status' => 'active',
        ]);

        $vendorRole = Role::findOrCreate('Vendor', 'web');
        $vendorUser = User::factory()->create(['role_id' => $vendorRole->id, 'phone' => '0773000001']);
        $vendorUser->assignRole($vendorRole);
        $vendor = $vendorUser->vendor()->firstOrFail();
        $vendor->update([
            'store_name' => 'Profile Vendor',
            'phone' => $vendorUser->phone,
            'address' => 'Old Store',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);

        $this->actingAs($vendorUser)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Delivery District')
            ->assertDontSee('View delivery fee criteria');

        $this->actingAs($vendorUser)
            ->patch('/profile', [
                'name' => $vendorUser->name,
                'email' => $vendorUser->email,
                'phone' => '0773000099',
                'address' => '22 Market Road',
                'city' => 'Kandy',
                'province' => 'Central',
                'latitude' => '7.2906000',
                'longitude' => '80.6337000',
                'formatted_address' => '22 Market Road, Kandy',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Colombo', $vendor->refresh()->district);
        $this->assertSame('0773000099', $vendor->phone);

        $riderRole = Role::findOrCreate('Rider', 'web');
        $riderUser = User::factory()->create(['role_id' => $riderRole->id, 'phone' => '0774000001']);
        $riderUser->assignRole($riderRole);
        $rider = $riderUser->rider()->firstOrFail();
        $rider->update([
            'vehicle_type' => 'motorbike',
            'address' => 'Old Base',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'availability_status' => 'available',
            'verification_status' => 'verified',
        ]);

        $this->actingAs($riderUser)
            ->patch('/profile', [
                'name' => $riderUser->name,
                'email' => $riderUser->email,
                'phone' => $riderUser->phone,
                'address' => '5 Lake Road',
                'city' => 'Kandy',
                'district' => 'Kandy',
                'province' => 'Central',
                'latitude' => '7.2906000',
                'longitude' => '80.6337000',
                'formatted_address' => '5 Lake Road, Kandy',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Kandy', $rider->refresh()->district);
        $this->assertSame('5 Lake Road, Kandy', $rider->formatted_address);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_vendor_self_deletion_removes_the_vendor_profile_and_store_from_active_lists(): void
    {
        $role = Role::findOrCreate('Vendor', 'web');
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->assignRole($role);
        $vendor = $user->vendor()->firstOrFail();
        $vendor->update([
            'store_name' => 'Deleted Store',
            'phone' => '0775000001',
            'address' => '1 Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertSoftDeleted($user);
        $this->assertSoftDeleted($vendor);
        $this->assertSame(0, Vendor::query()->whereKey($vendor)->count());
    }

    public function test_admin_can_delete_customer_and_vendor_accounts(): void
    {
        $adminRole = Role::findOrCreate('Admin', 'web');
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $customerRole = Role::findOrCreate('Customer', 'web');
        $customerUser = User::factory()->create(['role_id' => $customerRole->id]);
        $customerUser->assignRole($customerRole);
        $customer = $customerUser->customer()->firstOrFail();
        $customer->update([
            'first_name' => 'Delete',
            'phone' => '0775000002',
            'status' => 'active',
        ]);

        $vendorRole = Role::findOrCreate('Vendor', 'web');
        $vendorUser = User::factory()->create(['role_id' => $vendorRole->id]);
        $vendorUser->assignRole($vendorRole);
        $vendor = $vendorUser->vendor()->firstOrFail();
        $vendor->update([
            'store_name' => 'Remove Me',
            'phone' => '0775000003',
            'address' => '2 Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'));
        $this->actingAs($admin)
            ->delete(route('admin.vendors.destroy', $vendor))
            ->assertRedirect(route('admin.vendors.index'));

        $this->assertSoftDeleted($customerUser);
        $this->assertSoftDeleted($customer);
        $this->assertSoftDeleted($vendorUser);
        $this->assertSoftDeleted($vendor);
    }

    public function test_admin_can_remove_a_legacy_vendor_whose_user_was_already_deleted(): void
    {
        $adminRole = Role::findOrCreate('Admin', 'web');
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $vendorRole = Role::findOrCreate('Vendor', 'web');
        $vendorUser = User::factory()->create(['role_id' => $vendorRole->id]);
        $vendorUser->assignRole($vendorRole);
        $vendor = $vendorUser->vendor()->firstOrFail();
        $vendor->update([
            'store_name' => 'Legacy Store',
            'phone' => '0775000004',
            'address' => '3 Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);
        $vendorUser->delete();

        $this->actingAs($admin)
            ->delete(route('admin.vendors.destroy', $vendor))
            ->assertRedirect(route('admin.vendors.index'));

        $this->assertSoftDeleted($vendor);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
