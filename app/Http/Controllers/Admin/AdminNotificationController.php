<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = Notification::query()
            ->with('user')
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification, NotificationService $notifications): RedirectResponse
    {
        $notifications->markRead($notification);

        return back()->with('status', 'Notification marked as read.');
    }
}
