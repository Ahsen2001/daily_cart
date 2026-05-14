<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, SupportTicket $supportTicket): bool
    {
        return $supportTicket->user_id === $user->id;
    }

    public function reply(User $user, SupportTicket $supportTicket): bool
    {
        return $supportTicket->user_id === $user->id && ! in_array($supportTicket->status, ['closed'], true);
    }
}
