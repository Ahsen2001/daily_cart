<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\OtpService;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request, OtpService $otps, PhoneVerificationService $phones): JsonResponse
    {
        $request->validate([
            'role' => ['nullable', Rule::in(['customer'])],
        ], [
            'role.in' => 'Use the dedicated Vendor or Rider registration endpoint for this role.',
        ]);

        return $this->registerCustomer($request, $otps, $phones);
    }

    public function registerCustomer(
        Request $request,
        OtpService $otps,
        PhoneVerificationService $phones
    ): JsonResponse {
        $validated = $request->validate($this->commonRegistrationRules());

        $user = DB::transaction(function () use ($validated): User {
            $role = Role::findOrCreate('Customer', 'web');
            $user = $this->createUser($validated, $role, 'active');

            Customer::updateOrCreate(['user_id' => $user->id], [
                'first_name' => $validated['name'],
                'phone' => $validated['phone'],
                'status' => 'active',
            ]);

            return $user;
        });

        return $this->completeRegistration($user, $validated, $otps, $phones);
    }

    public function registerVendor(
        Request $request,
        OtpService $otps,
        PhoneVerificationService $phones
    ): JsonResponse {
        $validated = $request->validate([
            ...$this->commonRegistrationRules(),
            'store_name' => ['required', 'string', 'max:255'],
            'business_registration_no' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(Vendor::class, 'business_registration_no')->withoutTrashed(),
            ],
            ...$this->homeBaseRules(),
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $role = Role::findOrCreate('Vendor', 'web');
            $user = $this->createUser($validated, $role, 'pending');

            Vendor::updateOrCreate(['user_id' => $user->id], [
                'store_name' => $validated['store_name'],
                'business_registration_no' => $validated['business_registration_no'] ?? null,
                'phone' => $validated['phone'],
                ...$this->homeBaseValues($validated),
                'status' => 'pending',
            ]);

            return $user;
        });

        return $this->completeRegistration($user, $validated, $otps, $phones);
    }

    public function registerRider(
        Request $request,
        OtpService $otps,
        PhoneVerificationService $phones
    ): JsonResponse {
        $validated = $request->validate([
            ...$this->commonRegistrationRules(),
            'vehicle_type' => ['required', Rule::in(['bicycle', 'motorbike', 'three_wheeler', 'van'])],
            'vehicle_number' => ['nullable', 'string', 'max:255'],
            'license_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(Rider::class, 'license_number')->withoutTrashed(),
            ],
            ...$this->homeBaseRules(),
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $role = Role::findOrCreate('Rider', 'web');
            $user = $this->createUser($validated, $role, 'pending');

            Rider::updateOrCreate(['user_id' => $user->id], [
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                ...$this->homeBaseValues($validated),
                'availability_status' => 'unavailable',
                'verification_status' => 'pending',
            ]);

            return $user;
        });

        return $this->completeRegistration($user, $validated, $otps, $phones);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()
            ->with(['roles', 'customer', 'vendor', 'rider'])
            ->where('email', $validated['email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->hasAnyRole(['Customer', 'Vendor', 'Rider'])) {
            return response()->json([
                'success' => false,
                'message' => 'This account cannot sign in to a DailyCart mobile app.',
            ], 403);
        }

        if (! in_array($user->status, ['active', 'pending'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not active.',
            ], 403);
        }

        $token = $this->issueToken($user, $validated['device_name'] ?? 'dailycart-mobile');

        return $this->authenticationResponse($user, $token, 'Signed in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $accessToken = $request->user()->currentAccessToken();

        if ($accessToken instanceof PersonalAccessToken) {
            PersonalAccessToken::query()->whereKey($accessToken->getKey())->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['roles', 'customer', 'vendor', 'rider']);

        return response()->json([
            'success' => true,
            'message' => 'Authenticated profile retrieved.',
            'user' => new UserResource($user),
            ...$this->accountState($user),
        ]);
    }

    private function completeRegistration(
        User $user,
        array $validated,
        OtpService $otps,
        PhoneVerificationService $phones
    ): JsonResponse {
        $user->load(['roles', 'customer', 'vendor', 'rider']);
        $otps->send($user, 'email_verification');
        $phones->send($user);
        $token = $this->issueToken($user, $validated['device_name'] ?? 'dailycart-mobile');

        return $this->authenticationResponse(
            $user,
            $token,
            'Account created. Verify your email and phone to continue.',
            201,
        );
    }

    private function createUser(array $validated, Role $role, string $status): User
    {
        $user = User::create([
            'name' => $validated['name'],
            'role_id' => $role->id,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'status' => $status,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function authenticationResponse(
        User $user,
        NewAccessToken $token,
        string $message,
        int $status = 200
    ): JsonResponse {
        $plainTextToken = $token->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $message,
            'token_type' => 'Bearer',
            'access_token' => $plainTextToken,
            'token' => $plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toISOString(),
            'user' => new UserResource($user),
            ...$this->accountState($user),
        ], $status);
    }

    private function accountState(User $user): array
    {
        $role = $user->getRoleNames()->first();
        $requiresApproval = match ($role) {
            'Vendor' => $user->vendor?->status !== 'approved',
            'Rider' => $user->rider?->verification_status !== 'verified',
            default => false,
        };

        return [
            'requires_verification' => ! $user->hasVerifiedEmail() || ! $user->hasVerifiedPhone(),
            'requires_approval' => $requiresApproval,
        ];
    }

    private function issueToken(User $user, string $deviceName): NewAccessToken
    {
        $roleAbility = str($user->getRoleNames()->first() ?? 'unknown')
            ->lower()
            ->replace(' ', '-')
            ->toString();
        $expiration = (int) config('sanctum.expiration');
        $expiresAt = $expiration > 0 ? now()->addMinutes($expiration) : null;

        $user->tokens()->where('name', $deviceName)->delete();

        return $user->createToken(
            $deviceName,
            ['auth', 'profile', 'verification', $roleAbility],
            $expiresAt,
        );
    }

    private function commonRegistrationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->withoutTrashed(),
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique(User::class, 'phone')->withoutTrashed(),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function homeBaseRules(): array
    {
        return [
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function homeBaseValues(array $validated): array
    {
        return [
            'address' => $validated['address'],
            'city' => $validated['city'],
            'district' => $validated['district'],
            'province' => $validated['province'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'formatted_address' => $validated['formatted_address'] ?? null,
        ];
    }
}
