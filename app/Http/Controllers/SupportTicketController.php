<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        return view('support-tickets.index', [
            'tickets' => $request->user()->supportTickets()->with('order')->latest()->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        return view('support-tickets.create', [
            'orders' => $request->user()->customer?->orders()->latest()->get() ?? collect(),
        ]);
    }

    public function store(StoreSupportTicketRequest $request, SupportTicketService $tickets): RedirectResponse
    {
        $ticket = $tickets->create($request->user(), $request->validated());

        return redirect()->route('support.tickets.show', $ticket)->with('status', 'Support ticket created.');
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        return view('support-tickets.show', [
            'ticket' => $ticket->load(['order', 'user', 'assignedAdmin', 'replies.user']),
        ]);
    }
}
