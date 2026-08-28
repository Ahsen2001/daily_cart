<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use App\View\NotificationPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'view' => ['nullable', 'in:all,unread,action'],
            'category' => ['nullable', 'in:'.implode(',', array_keys(NotificationPresenter::CATEGORIES))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $baseQuery = $request->user()->notifications();
        $query = (clone $baseQuery)->with(['order.statusHistories'])
            ->when(($filters['view'] ?? 'all') === 'unread', fn ($builder) => $builder->whereNull('read_at'))
            ->when(($filters['view'] ?? 'all') === 'action', fn ($builder) => NotificationPresenter::applyActionRequired($builder))
            ->when(filled($filters['category'] ?? null), fn ($builder) => NotificationPresenter::applyCategory($builder, $filters['category']))
            ->when(filled($filters['q'] ?? null), function ($builder) use ($filters) {
                $search = '%'.$filters['q'].'%';
                $builder->where(fn ($inner) => $inner->where('title', 'like', $search)->orWhere('message', 'like', $search));
            });

        return view('notifications.index', [
            'notifications' => $query->latest()->paginate(20)->withQueryString(),
            'notificationStats' => [
                'all' => (clone $baseQuery)->count(),
                'unread' => (clone $baseQuery)->whereNull('read_at')->count(),
                'action' => NotificationPresenter::applyActionRequired(clone $baseQuery)->count(),
            ],
            'categories' => NotificationPresenter::CATEGORIES,
            'activeView' => $filters['view'] ?? 'all',
            'currentUser' => $request->user()->loadMissing('role'),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
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
