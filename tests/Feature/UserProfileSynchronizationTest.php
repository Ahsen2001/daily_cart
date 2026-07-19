<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_primary_role_creates_its_required_profile(): void
    {
        $this->seed(RoleSeeder::class);

        $accounts = [
            'Customer' => 'customer@example.test',
            'Vendor' => 'vendor@example.test',
            'Rider' => 'rider@example.test',
            'Admin' => 'admin@example.test',
            'Super Admin' => 'super-admin@example.test',
        ];

        foreach ($accounts as $role => $email) {
            $user = User::create([
                'name' => $role.' Account',
                'email' => $email,
                'password' => Hash::make('Password@123'),
                'status' => 'active',
            ]);

            $user->assignRole($role);

            $this->assertDatabaseHas('users', ['id' => $user->id]);
        }

        $this->assertDatabaseHas('customers', ['user_id' => User::where('email', $accounts['Customer'])->value('id')]);
        $this->assertDatabaseHas('vendors', ['user_id' => User::where('email', $accounts['Vendor'])->value('id')]);
        $this->assertDatabaseHas('riders', ['user_id' => User::where('email', $accounts['Rider'])->value('id')]);
        $this->assertDatabaseHas('admins', ['user_id' => User::where('email', $accounts['Admin'])->value('id'), 'access_level' => 'admin']);
        $this->assertDatabaseHas('admins', ['user_id' => User::where('email', $accounts['Super Admin'])->value('id'), 'access_level' => 'super_admin']);
    }
}
