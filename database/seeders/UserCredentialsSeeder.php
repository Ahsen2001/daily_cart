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
            ['email' => 'uahsens2@gmail.com'],
            [
                'name' => 'Uahsens Admin',
                'role_id' => $adminRole->id,
                'phone' => '0701000002',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        $adminUser->forceFill([
            'role_id' => $adminRole->id,
            'status' => 'active',
            'email_verified_at' => $adminUser->email_verified_at ?? now(),
            'phone_verified_at' => $adminUser->phone_verified_at ?? now(),
        ])->save();

        $adminUser->assignRole($adminRole->name);

        // 2. Vendor
        $vendorRole = Role::query()->where('name', 'Vendor')->firstOrFail();
        $vendorUser = User::firstOrCreate(
            ['email' => 'uahsens2001@gmail.com'],
            [
                'name' => 'Uahsens Vendor',
                'role_id' => $vendorRole->id,
                'phone' => '0701000003',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );
        $vendorUser->forceFill([
            'role_id' => $vendorRole->id,
            'email_verified_at' => $vendorUser->email_verified_at ?? now(),
            'phone_verified_at' => $vendorUser->phone_verified_at ?? now(),
        ])->save();
        $vendorUser->assignRole($vendorRole->name);

        Vendor::updateOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'store_name' => 'DailyCart Vendor Store',
                'business_registration_no' => 'BR-12345',
                'phone' => '0701000003',
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
            ['email' => 'rifkabanu870@gmail.com'],
            [
                'name' => 'Rifkabanu Customer',
                'role_id' => $customerRole->id,
                'phone' => '0701000004',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );
        $customerUser->forceFill([
            'role_id' => $customerRole->id,
            'email_verified_at' => $customerUser->email_verified_at ?? now(),
            'phone_verified_at' => $customerUser->phone_verified_at ?? now(),
        ])->save();
        $customerUser->assignRole($customerRole->name);

        $customer = Customer::updateOrCreate(
            ['user_id' => $customerUser->id],
            [
                'first_name' => 'Rifkabanu',
                'last_name' => 'Customer',
                'phone' => '0701000004',
                'status' => 'active',
            ]
        );

        $customer->addresses()->firstOrCreate(
            ['address_line_1' => '456 customer Road'],
            [
                'label' => 'Home',
                'recipient_name' => 'Rifkabanu Customer',
                'phone' => '0754603008',
                'city' => 'Batticaloa',
                'district' => 'Batticaloa',
                'is_default' => true,
            ]
        );

        // 4. Rider
        $riderRole = Role::query()->where('name', 'Rider')->firstOrFail();
        $riderUser = User::firstOrCreate(
            ['email' => 'ofnaaa@gmai.com'],
            [
                'name' => 'Ofnaaa Rider',
                'role_id' => $riderRole->id,
                'phone' => '0701000005',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );
        $riderUser->forceFill([
            'role_id' => $riderRole->id,
            'email_verified_at' => $riderUser->email_verified_at ?? now(),
            'phone_verified_at' => $riderUser->phone_verified_at ?? now(),
        ])->save();
        $riderUser->assignRole($riderRole->name);

        Rider::updateOrCreate(
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
