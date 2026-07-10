<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('subject', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $message): View
    {
        if ($message->status === 'pending') {
            $message->update(['status' => 'read']);
        }

        return view('admin.contact-messages.show', compact('message'));
    }

    public function update(Request $request, ContactMessage $message): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,read,replied,closed'],
        ]);

        $message->update($validated);

        return back()->with('status', 'Contact message status updated.');
    }
}
