<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotificationCenterLayoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function rolePages(): array
    {
        return [
            'customer' => ['Customer', '/notifications', 'Your notification center'],
            'vendor' => ['Vendor', '/notifications', 'Vendor action center'],
            'rider' => ['Rider', '/notifications', 'Rider action center'],
            'admin' => ['Admin', '/admin/notifications', 'Operations action center'],
            'super admin' => ['Super Admin', '/admin/notifications', 'Operations action center'],
        ];
    }

    #[DataProvider('rolePages')]
    public function test_notification_center_renders_the_responsive_role_layout(
        string $roleName,
        string $url,
        string $heading,
    ): void {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertSee($heading)
            ->assertSee('Role-aware inbox')
            ->assertSee('text-white', escape: false)
            ->assertSee('grid min-w-0 gap-6', escape: false)
            ->assertSee('lg:grid-cols-[minmax(0,1fr)_auto]', escape: false)
            ->assertSee('w-full whitespace-nowrap', escape: false);
    }
}
