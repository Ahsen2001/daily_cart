<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketReplyRequest;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;

class SupportTicketReplyController extends Controller
{
    public function store(StoreSupportTicketReplyRequest $request, SupportTicket $ticket, SupportTicketService $tickets): RedirectResponse
    {
        $tickets->reply($ticket, $request->user(), $request->message, $request->file('attachment'));

        return back()->with('status', 'Reply added.');
    }
}
