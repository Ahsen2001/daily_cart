<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Customer',
            'Vendor',
            'Delivery Rider',
            'Admin',
            'Super Admin',
        ])->each(fn (string $role) => Role::findOrCreate($role, 'web'));
    }
}
