<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationDeliveryLog;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $deliverySummary = NotificationDeliveryLog::query()
            ->selectRaw('channel, status, count(*) as total')
            ->groupBy('channel', 'status')
            ->orderBy('channel')
            ->get()
            ->groupBy('channel');
        $recentDeliveries = NotificationDeliveryLog::query()
            ->with(['notification', 'user'])
            ->whereIn('status', [NotificationDeliveryLog::STATUS_FAILED, NotificationDeliveryLog::STATUS_QUEUED, NotificationDeliveryLog::STATUS_SKIPPED])
            ->latest('updated_at')
            ->limit(20)
            ->get();
        $channelHealth = [
            'email' => filled(config('mail.default')) && config('mail.default') !== 'log',
            'sms' => filled(config('services.sms.endpoint')) && filled(config('services.sms.token')),
            'firebase' => filled(config('services.firebase.project_id')) && filled(config('services.firebase.credentials')),
        ];

        return view('admin.notifications.index', compact('notifications', 'deliverySummary', 'recentDeliveries', 'channelHealth'));
    }

    public function markRead(Notification $notification, NotificationService $notifications): RedirectResponse
    {
        abort_unless($notification->user_id === request()->user()->id, 403);
        $notifications->markRead($notification);

        return back()->with('status', 'Notification marked as read.');
    }

    public function retryDelivery(NotificationDeliveryLog $delivery, NotificationService $notifications): RedirectResponse
    {
        $notifications->retryDelivery($delivery);

        return back()->with('status', 'Notification delivery queued for retry.');
    }
}
