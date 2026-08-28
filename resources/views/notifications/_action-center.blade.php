@php
    use App\View\NotificationPresenter;
    $currentUser = $currentUser ?? request()->user();
    $roleName = strtolower((string) ($currentUser->role?->name ?? 'customer'));
    $isAdminCenter = $adminCenter ?? false;
    $indexRoute = $isAdminCenter ? 'admin.notifications.index' : 'notifications.index';
    $readRoute = $isAdminCenter ? 'admin.notifications.read' : 'notifications.read';
    $readAllRoute = $isAdminCenter ? 'admin.notifications.read-all' : 'notifications.read-all';
    $roleCopy = match ($roleName) {
        'vendor' => ['Vendor action center', 'Prioritize new orders, refunds, payouts and inventory alerts.'],
        'rider' => ['Rider action center', 'Keep deliveries, earnings, ratings and account updates in view.'],
        'admin', 'super admin' => ['Operations action center', 'Triage orders, approvals, finance and platform activity from one place.'],
        default => ['Your notification center', 'Track orders, payments, refunds and support without missing the next step.'],
    };
    $roleAccent = match ($roleName) {
        'rider' => 'from-indigo-950 via-indigo-800 to-sky-700',
        'admin', 'super admin' => 'from-slate-950 via-slate-800 to-emerald-800',
        'vendor' => 'from-emerald-950 via-emerald-800 to-teal-700',
        default => 'from-emerald-950 via-green-800 to-emerald-600',
    };
    $groups = $notifications->getCollection()->groupBy(function ($notification) {
        if ($notification->created_at->isToday()) return 'Today';
        if ($notification->created_at->isYesterday()) return 'Yesterday';
        return 'Earlier';
    });
@endphp

<section class="overflow-hidden rounded-[1.75rem] border border-emerald-100 bg-white shadow-xl shadow-emerald-950/5">
    <div class="relative overflow-hidden bg-gradient-to-br {{ $roleAccent }} px-6 py-7 text-white sm:px-8">
        <div class="absolute -right-16 -top-24 h-64 w-64 rounded-full border border-white/10 bg-white/5"></div>
        <div class="absolute -bottom-24 right-32 h-48 w-48 rounded-full border border-white/10"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-emerald-50"><span class="h-2 w-2 rounded-full bg-emerald-300"></span>{{ __('Role-aware inbox') }}</div>
                <h2 class="text-2xl font-black tracking-tight sm:text-3xl">{{ __($roleCopy[0]) }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/75 sm:text-base">{{ __($roleCopy[1]) }}</p>
            </div>
            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                @foreach (['all' => 'All', 'unread' => 'Unread', 'action' => 'Action'] as $key => $label)
                    <a href="{{ route($indexRoute, array_filter(['view' => $key, 'category' => request('category'), 'q' => request('q')])) }}" class="min-w-20 rounded-2xl border px-3 py-2.5 text-center transition {{ $activeView === $key ? 'border-white bg-white text-slate-900 shadow-lg' : 'border-white/15 bg-white/10 text-white hover:bg-white/15' }}">
                        <span class="block text-lg font-black">{{ $notificationStats[$key] }}</span><span class="text-[11px] font-bold uppercase tracking-wider">{{ __($label) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-8">
        <form method="GET" action="{{ route($indexRoute) }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <input type="hidden" name="view" value="{{ $activeView }}">
            <label class="relative block"><span class="sr-only">{{ __('Search notifications') }}</span><svg class="absolute left-4 top-3.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke-width="2" stroke-linecap="round"/></svg><input name="q" value="{{ request('q') }}" placeholder="{{ __('Search messages, orders or updates') }}" class="w-full rounded-xl border-slate-200 bg-white py-3 pl-11 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-500"></label>
            <select name="category" class="rounded-xl border-slate-200 bg-white py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="">{{ __('All categories') }}</option>@foreach ($categories as $key => $label)<option value="{{ $key }}" @selected(request('category') === $key)>{{ __($label) }}</option>@endforeach</select>
            <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">{{ __('Apply filters') }}</button>
        </form>
        @if ($notificationStats['unread'] > 0)<form method="POST" action="{{ route($readAllRoute) }}" class="mt-3 text-right">@csrf @method('PATCH')<button class="text-sm font-bold text-emerald-700 hover:text-emerald-900">{{ __('Mark all as read') }} ✓</button></form>@endif
    </div>

    <div class="p-5 sm:p-8">
        @forelse ($groups as $groupLabel => $groupNotifications)
            <section class="mb-8 last:mb-0">
                <div class="mb-3 flex items-center gap-3"><h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">{{ __($groupLabel) }}</h3><div class="h-px flex-1 bg-slate-100"></div></div>
                <div class="space-y-3">
                    @foreach ($groupNotifications as $notification)
                        @php
                            $tone = NotificationPresenter::tone($notification);
                            $category = NotificationPresenter::category($notification);
                            $action = NotificationPresenter::action($notification, $currentUser);
                            $actionRequired = NotificationPresenter::requiresAction($notification);
                            $toneClasses = match ($tone) {
                                'danger' => ['bg-rose-50 text-rose-700 ring-rose-100', 'bg-rose-500', 'border-l-rose-400'],
                                'warning' => ['bg-amber-50 text-amber-800 ring-amber-100', 'bg-amber-500', 'border-l-amber-400'],
                                'success' => ['bg-emerald-50 text-emerald-700 ring-emerald-100', 'bg-emerald-500', 'border-l-emerald-400'],
                                default => ['bg-sky-50 text-sky-700 ring-sky-100', 'bg-sky-500', 'border-l-sky-400'],
                            };
                            $iconPath = match ($category) {
                                'orders' => 'M6 7h12l-1 12H7L6 7Zm3 0V5a3 3 0 0 1 6 0v2',
                                'delivery' => 'M3 7h11v9H3V7Zm11 3h4l3 3v3h-7v-6ZM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z',
                                'payments', 'finance', 'refunds' => 'M4 6h16v12H4V6Zm0 4h16M8 15h3',
                                'support' => 'M5 5h14v11H9l-4 3V5Z',
                                'reviews' => 'm12 3 2.6 5.3 5.9.9-4.2 4.1 1 5.8-5.3-2.8-5.3 2.8 1-5.8-4.2-4.1 5.9-.9L12 3Z',
                                default => 'M12 3a7 7 0 0 1 7 7v4l2 3H3l2-3v-4a7 7 0 0 1 7-7Zm-2 17h4',
                            };
                        @endphp
                        <article class="relative rounded-2xl border border-slate-200 border-l-4 {{ $toneClasses[2] }} bg-white p-4 transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5 {{ $notification->read_at ? 'opacity-80' : '' }}">
                            <div class="flex gap-3 sm:gap-4">
                                <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 {{ $toneClasses[0] }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $iconPath }}"/></svg>@unless ($notification->read_at)<span class="absolute -right-1 -top-1 h-3 w-3 rounded-full border-2 border-white {{ $toneClasses[1] }}"></span>@endunless</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div><div class="flex flex-wrap items-center gap-2"><span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider ring-1 {{ $toneClasses[0] }}">{{ __(NotificationPresenter::label($notification)) }}</span>@if ($actionRequired)<span class="rounded-full bg-slate-900 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-white">{{ __('Action required') }}</span>@endif</div><h4 class="mt-2 text-base font-black text-slate-900 sm:text-lg">{{ $notification->title }}</h4></div>
                                        <time class="shrink-0 text-xs font-medium text-slate-400"><x-local-time :date="$notification->created_at" /></time>
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                                    @if (filled($notification->data['email_details'] ?? null))<dl class="mt-4 grid gap-2 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">@foreach ($notification->data['email_details'] as $label => $value)<div><dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $label }}</dt><dd class="mt-0.5 font-semibold text-slate-700">{{ $value }}</dd></div>@endforeach</dl>@endif

                                    @if (NotificationPresenter::isOrderActivity($notification) && $notification->order)
                                        @php($histories = $notification->order->statusHistories->sortBy('created_at'))
                                        <details class="group mt-4 rounded-xl border border-slate-200 bg-slate-50/80">
                                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-bold text-slate-700"><span class="flex items-center gap-2"><svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h4l2-5 3 10 2-5h3"/></svg>{{ __('View smart activity timeline') }}</span><svg class="h-4 w-4 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></summary>
                                            <div class="border-t border-slate-200 px-5 py-4">
                                                <ol class="relative border-l-2 border-emerald-100 pl-5">@foreach ($histories as $history)<li class="relative pb-5 last:pb-0"><span class="absolute -left-[1.72rem] top-1 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 ring-2 ring-emerald-100"></span><div class="text-sm font-black capitalize text-slate-800">{{ str_replace('_', ' ', $history->status) }}</div>@if ($history->remarks)<div class="mt-0.5 text-xs leading-5 text-slate-500">{{ $history->remarks }}</div>@endif<time class="mt-1 block text-[11px] font-semibold text-slate-400">{{ $history->displayCreatedAt('M d, h:i A') }}</time></li>@endforeach</ol>
                                                <div class="mt-4 rounded-lg bg-white px-3 py-2 text-xs text-slate-500 ring-1 ring-slate-100">{{ __('Current status:') }} <strong class="capitalize text-slate-800">{{ str_replace('_', ' ', $notification->order->order_status) }}</strong></div>
                                            </div>
                                        </details>
                                    @endif

                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        @if ($action)<a href="{{ $action['url'] }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800">{{ __($action['label']) }} <span aria-hidden="true">→</span></a>@endif
                                        @if (!$notification->read_at || !$isAdminCenter)<form method="POST" action="{{ $notification->read_at && !$isAdminCenter ? route('notifications.unread', $notification) : route($readRoute, $notification) }}">@csrf @method('PATCH')<button class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-800">{{ $notification->read_at && !$isAdminCenter ? __('Mark unread') : __('Mark read') }}</button></form>@endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="py-16 text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700"><svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 5h14v11H9l-4 3V5Z"/><path d="m9 11 2 2 4-4"/></svg></div><h3 class="mt-4 text-lg font-black text-slate-900">{{ __('You are all caught up') }}</h3><p class="mt-1 text-sm text-slate-500">{{ __('No notifications match these filters.') }}</p></div>
        @endforelse
        @if ($notifications->hasPages())<div class="mt-8 border-t border-slate-100 pt-6">{{ $notifications->links() }}</div>@endif
    </div>
</section>
