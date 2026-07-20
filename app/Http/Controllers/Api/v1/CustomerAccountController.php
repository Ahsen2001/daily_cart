<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Notification;
use App\Models\SupportTicket;
use App\Services\AccountDeletionService;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerAccountController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
        ]);

        if ($validated['email'] !== $user->email) {
            $validated['email_verified_at'] = null;
        }
        if ($validated['phone'] !== $user->phone) {
            $validated['phone_verified_at'] = null;
        }
        $user->forceFill($validated)->save();
        $user->customer?->update([
            'first_name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => new UserResource($user->refresh()->load(['roles', 'customer'])),
        ]);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $validated = $request->validate(['photo' => ['required', 'image', 'max:5120']]);
        $user = $request->user();
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        $user->update(['profile_photo' => $validated['photo']->store('profile-photos', 'public')]);

        return response()->json([
            'message' => 'Profile photo updated.',
            'profile' => new UserResource($user->refresh()->load(['roles', 'customer'])),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $request->user()->update(['password' => Hash::make($validated['password'])]);
        $currentToken = $request->user()->currentAccessToken();
        if ($currentToken instanceof PersonalAccessToken) {
            $request->user()->tokens()->where('id', '!=', $currentToken->getKey())->delete();
        }

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function destroyAccount(Request $request, AccountDeletionService $accounts): JsonResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $accounts->delete($request->user());

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    public function notifications(Request $request): JsonResponse
    {
        return response()->json([
            'notifications' => $request->user()->notifications()->latest()->get(),
        ]);
    }

    public function readNotification(Request $request, Notification $notification): JsonResponse
    {
        $this->ensureNotificationOwned($request, $notification);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function readAllNotifications(Request $request): JsonResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function deleteNotification(Request $request, Notification $notification): JsonResponse
    {
        $this->ensureNotificationOwned($request, $notification);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }

    public function tickets(Request $request): JsonResponse
    {
        return response()->json([
            'tickets' => $request->user()->supportTickets()->latest()->get(),
        ]);
    }

    public function createTicket(Request $request, SupportTicketService $tickets): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ]);
        $ticket = $tickets->create($request->user(), $validated);

        return response()->json([
            'message' => 'Support ticket created.',
            'ticket' => $this->ticketPayload($ticket),
        ], 201);
    }

    public function ticket(Request $request, SupportTicket $ticket): JsonResponse
    {
        $this->ensureTicketOwned($request, $ticket);

        return response()->json(['ticket' => $this->ticketPayload($ticket)]);
    }

    public function replyTicket(
        Request $request,
        SupportTicket $ticket,
        SupportTicketService $tickets
    ): JsonResponse {
        $this->ensureTicketOwned($request, $ticket);
        abort_if(in_array($ticket->status, ['closed', 'resolved'], true), 422, 'This ticket is closed.');
        $validated = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        $tickets->reply($ticket, $request->user(), $validated['message']);

        return response()->json([
            'message' => 'Reply added.',
            'ticket' => $this->ticketPayload($ticket->refresh()),
        ]);
    }

    public function closeTicket(
        Request $request,
        SupportTicket $ticket,
        SupportTicketService $tickets
    ): JsonResponse {
        $this->ensureTicketOwned($request, $ticket);

        return response()->json([
            'message' => 'Ticket closed.',
            'ticket' => $this->ticketPayload($tickets->close($ticket)),
        ]);
    }

    private function ticketPayload(SupportTicket $ticket): array
    {
        $ticket->loadMissing('replies.user');

        return [
            ...$ticket->only(['id', 'order_id', 'subject', 'message', 'priority', 'status', 'created_at']),
            'ticket_number' => 'TKT-'.str_pad((string) $ticket->id, 6, '0', STR_PAD_LEFT),
            'replies' => $ticket->replies->map(fn ($reply) => [
                'id' => $reply->id,
                'message' => $reply->message,
                'sender_name' => $reply->user?->name,
                'is_customer' => ! $reply->user?->isAdminUser(),
                'attachment' => $reply->attachment ? url('storage/'.$reply->attachment) : null,
                'created_at' => $reply->created_at,
            ]),
        ];
    }

    private function ensureNotificationOwned(Request $request, Notification $notification): void
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
    }

    private function ensureTicketOwned(Request $request, SupportTicket $ticket): void
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);
    }
}
