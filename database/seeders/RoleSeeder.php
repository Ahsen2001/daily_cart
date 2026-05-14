<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Super Admin',
            'Admin',
            'Vendor',
            'Rider',
            'Customer',
        ])->each(fn (string $role) => Role::findOrCreate($role, 'web'));
    }
}
