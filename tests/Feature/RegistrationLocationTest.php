<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Rider;
use App\Models\Vendor;
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
}
