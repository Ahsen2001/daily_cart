<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Rider;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserCredentialsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        $adminRole = Role::query()->where('name', 'Admin')->firstOrFail();
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@dailycart.lk'],
            [
                'name' => 'DailyCart Admin',
                'role_id' => $adminRole->id,
                'phone' => '0700000001',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
            ]
        );

        $adminUser->forceFill([
            'role_id' => $adminRole->id,
            'status' => 'active',
        ])->save();

        $adminUser->assignRole($adminRole->name);

        // 2. Vendor
        $vendorRole = Role::query()->where('name', 'Vendor')->firstOrFail();
        $vendorUser = User::firstOrCreate(
            ['email' => 'afrijhaque@gmail.com'],
            [
                'name' => 'DailyCart Vendor',
                'role_id' => $vendorRole->id,
                'phone' => '0711111111',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
            ]
        );
        $vendorUser->assignRole($vendorRole->name);

        Vendor::firstOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'store_name' => 'DailyCart Vendor Store',
                'business_registration_no' => 'BR-12345',
                'phone' => '0711111111',
                'address' => '123 Store Street, Colombo',
                'city' => 'Batticaloa',
                'district' => 'Colombo',
                'commission_rate' => 10.00,
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // 3. Customer
        $customerRole = Role::query()->where('name', 'Customer')->firstOrFail();
        $customerUser = User::firstOrCreate(
            ['email' => 'uahsens1@gmail.com'],
            [
                'name' => 'DailyCart Customer',
                'role_id' => $customerRole->id,
                'phone' => '0722222222',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
            ]
        );
        $customerUser->assignRole($customerRole->name);

        $customer = Customer::firstOrCreate(
            ['user_id' => $customerUser->id],
            [
                'first_name' => 'DailyCart',
                'last_name' => 'Customer',
                'phone' => '0722222222',
                'status' => 'active',
            ]
        );

        $customer->addresses()->firstOrCreate(
            ['address_line_1' => '456 customer Road'],
            [
                'label' => 'Home',
                'recipient_name' => 'DailyCart Customer',
                'phone' => '0722222222',
                'city' => 'Colombo',
                'district' => 'Colombo',
                'is_default' => true,
            ]
        );

        // 4. Rider
        $riderRole = Role::query()->where('name', 'Rider')->firstOrFail();
        $riderUser = User::firstOrCreate(
            ['email' => 'uahsens2001@gmail.com'],
            [
                'name' => 'DailyCart Rider',
                'role_id' => $riderRole->id,
                'phone' => '0733333333',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
            ]
        );
        $riderUser->assignRole($riderRole->name);

        Rider::firstOrCreate(
            ['user_id' => $riderUser->id],
            [
                'vehicle_type' => 'motorbike',
                'vehicle_number' => 'WP-AB-1234',
                'license_number' => 'L-123456',
                'availability_status' => 'available',
                'verification_status' => 'verified',
            ]
        );
    }
}
