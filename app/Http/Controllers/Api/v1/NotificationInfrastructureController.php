<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NotificationInfrastructureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $role = $this->appRole($request);
        $validated = $request->validate([
            'unread_only' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $notifications = $request->user()->notifications()
            ->where(fn (Builder $query) => $query
                ->where('app_role', $role)
                ->orWhereNull('app_role'))
            ->when($validated['unread_only'] ?? false, fn (Builder $query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate((int) ($validated['per_page'] ?? 30));

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $request->user()->notifications()
                ->where(fn (Builder $query) => $query
                    ->where('app_role', $role)
                    ->orWhereNull('app_role'))
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        $this->ensureOwnedForRole($request, $notification);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification->refresh(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $role = $this->appRole($request);
        $request->user()->notifications()
            ->where(fn (Builder $query) => $query
                ->where('app_role', $role)
                ->orWhereNull('app_role'))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $this->ensureOwnedForRole($request, $notification);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }

    public function registerDevice(Request $request): JsonResponse
    {
        $validated = $this->validateDevice($request);
        $role = $this->appRole($request, $validated['app_role'] ?? null);

        $device = DeviceToken::updateOrCreate(
            ['token' => $validated['device_token']],
            [
                'user_id' => $request->user()->id,
                'device_id' => $validated['device_id'],
                'app_role' => $role,
                'platform' => $validated['platform'],
                'app_version' => $validated['app_version'] ?? null,
                'refreshed_at' => now(),
                'last_used_at' => now(),
                'revoked_at' => null,
            ],
        );

        $this->revokeSupersededTokens($device);
        NotificationPreference::firstOrCreate([
            'user_id' => $request->user()->id,
            'app_role' => $role,
        ]);

        return response()->json([
            'message' => 'Device token registered.',
            'device_token' => $this->devicePayload($device),
        ], $device->wasRecentlyCreated ? 201 : 200);
    }

    public function refreshDevice(Request $request): JsonResponse
    {
        $validated = $this->validateDevice($request, true);
        $role = $this->appRole($request, $validated['app_role'] ?? null);

        DeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('app_role', $role)
            ->where(function (Builder $query) use ($validated): void {
                $query->where('device_id', $validated['device_id']);
                if (filled($validated['old_device_token'] ?? null)) {
                    $query->orWhere('token', $validated['old_device_token']);
                }
            })
            ->where('token', '!=', $validated['device_token'])
            ->update(['revoked_at' => now()]);

        $request->request->remove('old_device_token');

        return $this->registerDevice($request);
    }

    public function revokeDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => ['nullable', 'string', 'max:4096', 'required_without:device_id'],
            'device_id' => ['nullable', 'string', 'max:191', 'required_without:device_token'],
            'app_role' => ['nullable', Rule::in(['customer', 'vendor', 'rider'])],
        ]);
        $role = $this->appRole($request, $validated['app_role'] ?? null);

        $updated = DeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('app_role', $role)
            ->when(
                filled($validated['device_token'] ?? null),
                fn (Builder $query) => $query->where('token', $validated['device_token'])
            )
            ->when(
                filled($validated['device_id'] ?? null),
                fn (Builder $query) => $query->where('device_id', $validated['device_id'])
            )
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return response()->json([
            'message' => 'Device token revoked.',
            'revoked_count' => $updated,
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        $preference = NotificationPreference::firstOrCreate([
            'user_id' => $request->user()->id,
            'app_role' => $this->appRole($request),
        ]);

        return response()->json(['preferences' => $preference]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_enabled' => ['sometimes', 'boolean'],
            'order_updates' => ['sometimes', 'boolean'],
            'delivery_updates' => ['sometimes', 'boolean'],
            'wallet_updates' => ['sometimes', 'boolean'],
            'support_updates' => ['sometimes', 'boolean'],
            'promotions' => ['sometimes', 'boolean'],
            'app_role' => ['nullable', Rule::in(['customer', 'vendor', 'rider'])],
        ]);
        $role = $this->appRole($request, $validated['app_role'] ?? null);
        unset($validated['app_role']);

        $preference = NotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id, 'app_role' => $role],
            $validated,
        );

        return response()->json([
            'message' => 'Notification preferences updated.',
            'preferences' => $preference,
        ]);
    }

    private function validateDevice(Request $request, bool $refresh = false): array
    {
        return $request->validate([
            'device_token' => ['required', 'string', 'max:4096'],
            'old_device_token' => [$refresh ? 'nullable' : 'prohibited', 'string', 'max:4096'],
            'device_id' => ['required', 'string', 'max:191'],
            'app_role' => ['nullable', Rule::in(['customer', 'vendor', 'rider'])],
            'platform' => ['required', Rule::in(['android', 'ios'])],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);
    }

    private function appRole(Request $request, ?string $requested = null): string
    {
        $routeRole = match (true) {
            $request->is('api/v1/vendor/*') => 'vendor',
            $request->is('api/v1/rider/*') => 'rider',
            default => $requested,
        };
        $userRole = strtolower((string) ($request->user()->role?->name
            ?? $request->user()->roles()->value('name')));

        if (! in_array($userRole, ['customer', 'vendor', 'rider'], true)) {
            throw ValidationException::withMessages([
                'app_role' => 'This account cannot register a mobile app device.',
            ]);
        }

        if ($routeRole !== null && $routeRole !== $userRole) {
            throw ValidationException::withMessages([
                'app_role' => 'The app role does not match the authenticated account.',
            ]);
        }

        return $userRole;
    }

    private function revokeSupersededTokens(DeviceToken $current): void
    {
        DeviceToken::query()
            ->where('user_id', $current->user_id)
            ->where('app_role', $current->app_role)
            ->where('device_id', $current->device_id)
            ->whereKeyNot($current->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    private function ensureOwnedForRole(Request $request, Notification $notification): void
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        abort_unless(
            $notification->app_role === null || $notification->app_role === $this->appRole($request),
            404,
        );
    }

    private function devicePayload(DeviceToken $device): array
    {
        return [
            'id' => $device->id,
            'device_id' => $device->device_id,
            'app_role' => $device->app_role,
            'platform' => $device->platform,
            'app_version' => $device->app_version,
            'refreshed_at' => $device->refreshed_at?->toISOString(),
        ];
    }
}
