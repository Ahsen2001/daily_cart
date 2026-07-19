<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Rider;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AccountDeletionService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_saves_the_default_map_location(): void
    {
        $this->seed(RoleSeeder::class);

        $this->post('/register', [
            'name' => 'Map Customer',
            'first_name' => 'Map',
            'last_name' => 'Customer',
            'email' => 'map.customer@example.com',
            'phone' => '0771000001',
            'address_line_1' => 'Main Street',
            'city' => 'Batticaloa',
            'district' => 'Batticaloa',
            'province' => 'Eastern',
            'latitude' => '7.7170000',
            'longitude' => '81.7000000',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        $address = Address::query()->sole();

        $this->assertSame('7.7170000', $address->latitude);
        $this->assertSame('81.7000000', $address->longitude);
        $this->assertTrue($address->is_default);
    }

    public function test_vendor_registration_saves_the_store_map_location(): void
    {
        $this->seed(RoleSeeder::class);

        $this->post('/vendor/register', [
            'name' => 'Vendor Owner',
            'store_name' => 'Mapped Market',
            'email' => 'map.vendor@example.com',
            'phone' => '0771000002',
            'address' => 'Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'province' => 'Western',
            'latitude' => '6.9271000',
            'longitude' => '79.8612000',
            'formatted_address' => 'Market Road, Colombo, Sri Lanka',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        $vendor = Vendor::query()->sole();

        $this->assertSame('6.9271000', $vendor->latitude);
        $this->assertSame('79.8612000', $vendor->longitude);
        $this->assertSame('Market Road, Colombo, Sri Lanka', $vendor->formatted_address);
    }

    public function test_rider_registration_saves_the_home_base_map_location(): void
    {
        $this->seed(RoleSeeder::class);

        $this->post('/rider/register', [
            'name' => 'Map Rider',
            'email' => 'map.rider@example.com',
            'phone' => '0771000003',
            'vehicle_type' => 'motorbike',
            'vehicle_number' => 'WP MAP 1234',
            'address' => 'Lake Road',
            'city' => 'Kandy',
            'district' => 'Kandy',
            'province' => 'Central',
            'latitude' => '7.2906000',
            'longitude' => '80.6337000',
            'formatted_address' => 'Lake Road, Kandy, Sri Lanka',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        $rider = Rider::query()->sole();

        $this->assertSame('7.2906000', $rider->latitude);
        $this->assertSame('80.6337000', $rider->longitude);
        $this->assertSame('Lake Road, Kandy, Sri Lanka', $rider->formatted_address);
    }

    public function test_vendor_can_register_again_after_the_previous_account_is_deleted(): void
    {
        $this->seed(RoleSeeder::class);
        $role = Role::findByName('Vendor', 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'rejoin.vendor@example.com',
            'phone' => '0771000004',
        ]);
        $user->assignRole($role);
        Vendor::query()->create([
            'user_id' => $user->id,
            'store_name' => 'Previous Store',
            'business_registration_no' => 'BR-REJOIN-1',
            'phone' => $user->phone,
            'address' => 'Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'province' => 'Western',
            'status' => 'approved',
        ]);

        app(AccountDeletionService::class)->delete($user);

        $this->post('/vendor/register', [
            'name' => 'Returning Vendor',
            'store_name' => 'Returning Store',
            'business_registration_no' => 'BR-REJOIN-1',
            'email' => 'rejoin.vendor@example.com',
            'phone' => '0771000004',
            'address' => 'New Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'province' => 'Western',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, User::query()->where('email', 'rejoin.vendor@example.com')->count());
        $this->assertSame(1, Vendor::query()->where('business_registration_no', 'BR-REJOIN-1')->count());
    }
}
