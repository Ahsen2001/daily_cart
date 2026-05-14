<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function markRead(Notification $notification, NotificationService $notifications): RedirectResponse
    {
        $this->authorize('update', $notification);
        $notifications->markRead($notification);

        return back()->with('status', 'Notification marked as read.');
    }

    public function markUnread(Notification $notification, NotificationService $notifications): RedirectResponse
    {
        $this->authorize('update', $notification);
        $notifications->markUnread($notification);

        return back()->with('status', 'Notification marked as unread.');
    }
}
