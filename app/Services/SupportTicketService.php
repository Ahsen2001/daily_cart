<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupportTicketService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function create(User $user, array $data): SupportTicket
    {
        if (! empty($data['order_id'])) {
            $this->ensureOrderBelongsToUser($user, (int) $data['order_id']);
        }

        return DB::transaction(function () use ($user, $data) {
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'order_id' => $data['order_id'] ?? null,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'priority' => $data['priority'],
                'status' => 'open',
            ]);

            $this->notifications->notifyAdmins(
                'Support ticket created',
                $user->name.' created ticket: '.$ticket->subject,
                'support_ticket_created'
            );

            return $ticket;
        });
    }

    public function reply(SupportTicket $ticket, User $user, string $message, ?UploadedFile $attachment = null): SupportTicketReply
    {
        return DB::transaction(function () use ($ticket, $user, $message, $attachment) {
            $reply = $ticket->replies()->create([
                'user_id' => $user->id,
                'message' => $message,
                'attachment' => $attachment?->store('support-ticket-replies', 'public'),
            ]);

            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'in_progress']);
            }

            $recipient = $user->isAdminUser() ? $ticket->user : $ticket->assignedAdmin;

            if ($recipient) {
                $this->notifications->send(
                    $recipient,
                    'Support ticket reply',
                    'A reply was added to ticket: '.$ticket->subject,
                    'support_ticket_reply',
                    ['database', 'mail']
                );
            }

            return $reply;
        });
    }

    public function assign(SupportTicket $ticket, User $admin): SupportTicket
    {
        if (! $admin->isAdminUser()) {
            throw ValidationException::withMessages(['assigned_admin_id' => 'Selected user is not an admin.']);
        }

        $ticket->update([
            'assigned_admin_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        return $ticket->refresh();
    }

    public function close(SupportTicket $ticket): SupportTicket
    {
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return $ticket->refresh();
    }

    public function updateStatus(SupportTicket $ticket, string $status): SupportTicket
    {
        if (! in_array($status, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            throw ValidationException::withMessages(['status' => 'Invalid ticket status.']);
        }

        $ticket->update([
            'status' => $status,
            'closed_at' => $status === 'closed' ? now() : null,
        ]);

        return $ticket->refresh();
    }

    private function ensureOrderBelongsToUser(User $user, int $orderId): void
    {
        $ownsOrder = Order::query()
            ->whereKey($orderId)
            ->where('customer_id', $user->customer?->id)
            ->exists();

        if (! $ownsOrder) {
            throw ValidationException::withMessages(['order_id' => 'You can create support tickets only for your own orders.']);
        }
    }
}
