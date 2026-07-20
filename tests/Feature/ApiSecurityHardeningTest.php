<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\EmailOtp;
use App\Models\OtpVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_issues_an_expiring_scoped_token_and_requires_verification(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Unverified Customer',
            'email' => 'unverified@example.com',
            'phone' => '0772000001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'device_name' => 'test-phone',
        ])->assertCreated();

        $token = PersonalAccessToken::findToken($response->json('token'));

        $this->assertNotNull($token);
        $this->assertSame('test-phone', $token->name);
        $this->assertEqualsCanonicalizing(
            ['auth', 'profile', 'verification', 'customer'],
            $token->abilities
        );
        $this->assertNotNull($token->expires_at);
        $this->assertNull($token->tokenable->email_verified_at);
        $this->assertNull($token->tokenable->phone_verified_at);

        $this->withToken($response->json('token'))->getJson('/api/v1/profile')->assertOk();
        $this->withToken($response->json('token'))->getJson('/api/v1/cart')->assertForbidden();

        Mail::assertQueued(OtpMail::class, fn (OtpMail $mail) => $mail->hasTo('unverified@example.com'));
    }

    public function test_email_and_phone_otp_endpoints_mark_the_user_verified(): void
    {
        $role = Role::findOrCreate('Customer', 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email_verified_at' => null,
            'phone' => '0772000002',
            'phone_verified_at' => null,
        ]);
        $user->assignRole($role);
        Sanctum::actingAs($user, ['verification']);

        EmailOtp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make('123456'),
            'purpose' => 'email_verification',
            'expires_at' => now()->addMinutes(10),
        ]);
        OtpVerification::create([
            'user_id' => $user->id,
            'type' => 'phone_verification',
            'otp' => Hash::make('654321'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/v1/email/verification-otp/verify', ['code' => '123456'])->assertOk();
        $this->postJson('/api/v1/phone/verification-otp/verify', ['code' => '654321'])->assertOk();

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasVerifiedPhone());
    }

    public function test_assigning_a_spatie_role_synchronizes_the_primary_role(): void
    {
        $role = Role::findOrCreate('Vendor', 'web');
        $user = User::factory()->create(['role_id' => null]);

        $user->assignRole($role);

        $this->assertSame($role->id, $user->refresh()->role_id);
        $this->assertTrue($user->hasPrimaryRole('Vendor'));
    }

    public function test_pending_vendor_and_rider_accounts_are_rejected_by_api_middleware(): void
    {
        $vendorRole = Role::findOrCreate('Vendor', 'web');
        $vendorUser = User::factory()->create(['role_id' => $vendorRole->id, 'phone' => '0772000003']);
        $vendorUser->assignRole($vendorRole);
        $vendorUser->vendor()->update([
            'store_name' => 'Pending Store',
            'phone' => $vendorUser->phone,
            'address' => '1 Vendor Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($vendorUser, ['vendor']);
        $this->getJson('/api/v1/vendor/overview')
            ->assertForbidden()
            ->assertJsonPath('message', 'Your vendor account is not approved.');

        $riderRole = Role::findOrCreate('Rider', 'web');
        $riderUser = User::factory()->create(['role_id' => $riderRole->id, 'phone' => '0772000004']);
        $riderUser->assignRole($riderRole);
        $riderUser->rider()->update([
            'vehicle_type' => 'motorbike',
            'availability_status' => 'unavailable',
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($riderUser, ['rider']);
        $this->getJson('/api/v1/rider/deliveries')
            ->assertForbidden()
            ->assertJsonPath('message', 'Your rider account is not approved.');
    }

    public function test_api_login_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/login', [
                'email' => 'rate-limit@example.com',
                'password' => 'incorrect-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/login', [
            'email' => 'rate-limit@example.com',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }
}
