<?php

namespace Tests\Feature;

use App\Models\EmailOtp;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiAuthenticationContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Mail::fake();
    }

    public function test_generic_registration_cannot_ignore_a_vendor_role(): void
    {
        $this->postJson('/api/v1/register', [
            ...$this->registrationPayload('legacy-vendor'),
            'role' => 'vendor',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'legacy-vendor@example.com',
        ]);
    }

    public function test_vendor_registration_persists_store_and_home_base_fields(): void
    {
        $response = $this->postJson('/api/v1/register/vendor', [
            ...$this->registrationPayload('vendor'),
            'store_name' => 'Daily Fresh',
            'business_registration_no' => 'BR-1001',
            ...$this->homeBasePayload(),
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.role', 'Vendor')
            ->assertJsonPath('user.approval_status', 'pending')
            ->assertJsonPath('requires_verification', true)
            ->assertJsonPath('requires_approval', true)
            ->assertJsonStructure([
                'access_token',
                'token',
                'expires_at',
                'user' => [
                    'is_email_verified',
                    'is_phone_verified',
                    'is_approved',
                ],
            ]);

        $this->assertSame($response->json('access_token'), $response->json('token'));

        $user = User::query()->where('email', 'vendor@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('Vendor'));
        $this->assertDatabaseHas('vendors', [
            'user_id' => $user->id,
            'store_name' => 'Daily Fresh',
            'business_registration_no' => 'BR-1001',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'province' => 'Western',
            'status' => 'pending',
        ]);
    }

    public function test_rider_registration_persists_vehicle_and_home_base_fields(): void
    {
        $this->postJson('/api/v1/register/rider', [
            ...$this->registrationPayload('rider'),
            'vehicle_type' => 'motorbike',
            'vehicle_number' => 'WP-ABC-1234',
            'license_number' => 'B1234567',
            ...$this->homeBasePayload(),
        ])
            ->assertCreated()
            ->assertJsonPath('user.role', 'Rider')
            ->assertJsonPath('requires_approval', true);

        $user = User::query()->where('email', 'rider@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('Rider'));
        $this->assertDatabaseHas('riders', [
            'user_id' => $user->id,
            'vehicle_type' => 'motorbike',
            'vehicle_number' => 'WP-ABC-1234',
            'license_number' => 'B1234567',
            'city' => 'Colombo',
            'verification_status' => 'pending',
        ]);
    }

    public function test_password_reset_uses_email_otp_and_revokes_existing_tokens(): void
    {
        $role = Role::findOrCreate('Customer', 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'recover@example.com',
            'password' => 'OldPassword123!',
        ]);
        $user->assignRole($role);
        $user->createToken('first-session', ['auth']);
        $user->createToken('second-session', ['auth']);

        EmailOtp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make('123456'),
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/v1/password/reset', [
            'email' => $user->email,
            'code' => '123456',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('NewPassword123!', $user->refresh()->password));
        $this->assertSame(0, PersonalAccessToken::query()
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', User::class)
            ->count());
    }

    public function test_forgot_password_does_not_reveal_whether_an_email_exists(): void
    {
        $unknown = $this->postJson('/api/v1/password/forgot', [
            'email' => 'unknown@example.com',
        ])->assertOk();

        $role = Role::findOrCreate('Customer', 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'known@example.com',
        ]);
        $user->assignRole($role);

        $known = $this->postJson('/api/v1/password/forgot', [
            'email' => $user->email,
        ])->assertOk();

        $this->assertSame($unknown->json('message'), $known->json('message'));
    }

    /** @return array<string, string> */
    private function registrationPayload(string $identity): array
    {
        return [
            'name' => ucfirst($identity).' User',
            'email' => $identity.'@example.com',
            'phone' => '077'.str_pad((string) abs(crc32($identity)), 7, '0', STR_PAD_LEFT),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'device_name' => 'test-'.$identity,
        ];
    }

    /** @return array<string, string|float> */
    private function homeBasePayload(): array
    {
        return [
            'address' => '1 Main Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'province' => 'Western',
            'latitude' => 6.9271,
            'longitude' => 79.8612,
            'formatted_address' => '1 Main Street, Colombo',
        ];
    }
}
