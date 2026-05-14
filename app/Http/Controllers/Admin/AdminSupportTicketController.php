<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::query()
            ->with(['user', 'order', 'assignedAdmin'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->priority))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket): View
    {
        return view('admin.support-tickets.show', [
            'ticket' => $ticket->load(['user', 'order', 'assignedAdmin', 'replies.user']),
            'admins' => User::whereHas('role', fn ($query) => $query->whereIn('name', ['Admin', 'Super Admin']))->get(),
        ]);
    }

    public function update(AdminSupportTicketRequest $request, SupportTicket $ticket, SupportTicketService $tickets): RedirectResponse
    {
        if ($request->filled('assigned_admin_id')) {
            $tickets->assign($ticket, User::findOrFail($request->assigned_admin_id));
        }

        if ($request->filled('status')) {
            $tickets->updateStatus($ticket, $request->status);
        }

        return back()->with('status', 'Ticket updated.');
    }
}
