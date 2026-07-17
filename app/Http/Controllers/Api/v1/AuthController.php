<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request, OtpService $otps): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $role = Role::findOrCreate('Customer', 'web');

            $user = User::create([
                'name' => $request->name,
                'role_id' => $role->id,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            Customer::create([
                'user_id' => $user->id,
                'first_name' => $request->name,
                'phone' => $request->phone,
                'status' => 'active',
            ]);

            $user->assignRole($role);

            return $user->load('roles', 'customer');
        });

        $otps->send($user, 'email_verification');
        $token = $this->issueToken($user, $validated['device_name'] ?? 'mobile-app');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status === 'suspended') {
            return response()->json(['message' => 'Your account is suspended.'], 403);
        }

        $token = $this->issueToken($user, $validated['device_name'] ?? 'mobile-app');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    private function issueToken(User $user, string $deviceName): string
    {
        $roleAbility = str($user->getRoleNames()->first() ?? 'unknown')->lower()->replace(' ', '-')->toString();
        $expiration = (int) config('sanctum.expiration');
        $expiresAt = $expiration > 0 ? now()->addMinutes($expiration) : null;

        return $user->createToken(
            $deviceName,
            ['auth', 'profile', 'verification', $roleAbility],
            $expiresAt
        )->plainTextToken;
    }
}
