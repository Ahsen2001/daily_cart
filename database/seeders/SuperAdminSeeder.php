<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Super Admin')->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => 'superadmin@dailycart.lk'],
            [
                'name' => 'DailyCart Super Admin',
                'role_id' => $role->id,
                'phone' => '0700000000',
                'password' => Hash::make('Password@123'),
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        $user->forceFill([
            'role_id' => $role->id,
            'status' => 'active',
            'email_verified_at' => $user->email_verified_at ?? now(),
            'phone_verified_at' => $user->phone_verified_at ?? now(),
        ])->save();

        $user->assignRole($role->name);
    }
}
