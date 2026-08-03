<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Notifications') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                <section class="mb-8 rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <h3 class="font-semibold text-gray-900">{{ __('Delivery channel health') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Database notifications are always stored. Email, SMS and push run independently through the queue.') }}</p>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach (['email' => 'Email', 'sms' => 'SMS', 'firebase' => 'Firebase push'] as $channel => $label)
                            @php($states = $deliverySummary->get($channel, collect()))
                            <div class="rounded-lg bg-white p-3 ring-1 ring-gray-100">
                                <div class="flex items-center justify-between"><span class="font-medium">{{ __($label) }}</span><span class="text-xs font-bold {{ $channelHealth[$channel] ? 'text-green-700' : 'text-orange-700' }}">{{ $channelHealth[$channel] ? __('Configured') : __('Needs setup') }}</span></div>
                                <p class="mt-2 text-xs text-gray-500">{{ __('Sent') }}: {{ $states->firstWhere('status', 'sent')?->total ?? 0 }} · {{ __('Queued') }}: {{ $states->firstWhere('status', 'queued')?->total ?? 0 }} · {{ __('Failed') }}: {{ $states->firstWhere('status', 'failed')?->total ?? 0 }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if ($recentDeliveries->isNotEmpty())
                    <section class="mb-8 overflow-x-auto rounded-xl border border-gray-100">
                        <div class="border-b border-gray-100 p-4"><h3 class="font-semibold text-gray-900">{{ __('Delivery queue issues') }}</h3><p class="mt-1 text-sm text-gray-600">{{ __('Review the reason before retrying. Retrying does not duplicate the database notification.') }}</p></div>
                        <table class="min-w-full divide-y divide-gray-100 text-sm"><thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-4 py-3">{{ __('Channel') }}</th><th class="px-4 py-3">{{ __('Recipient') }}</th><th class="px-4 py-3">{{ __('Status') }}</th><th class="px-4 py-3">{{ __('Reason') }}</th><th class="px-4 py-3"></th></tr></thead><tbody class="divide-y divide-gray-100">@foreach($recentDeliveries as $delivery)<tr><td class="px-4 py-3 font-medium">{{ strtoupper($delivery->channel) }}</td><td class="px-4 py-3">{{ $delivery->user?->email ?? $delivery->user?->phone ?? '-' }}</td><td class="px-4 py-3">{{ ucfirst($delivery->status) }}</td><td class="max-w-md px-4 py-3 text-xs text-gray-600">{{ $delivery->failure_reason ?: __('Waiting for a queue worker.') }}</td><td class="px-4 py-3 text-right">@if(in_array($delivery->status, ['failed', 'skipped'], true))<form method="POST" action="{{ route('admin.notifications.deliveries.retry', $delivery) }}">@csrf<button class="text-sm font-semibold text-indigo-700 underline">{{ __('Retry') }}</button></form>@endif</td></tr>@endforeach</tbody></table>
                    </section>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Title') }}</th><th class="px-3 py-2">{{ __('Message') }}</th><th class="px-3 py-2">{{ __('Read') }}</th><th class="px-3 py-2"></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($notifications as $notification)
                                <tr><td class="px-3 py-3 font-semibold">{{ $notification->title }}</td><td class="px-3 py-3 text-gray-600">{{ $notification->message }}</td><td class="px-3 py-3">{{ $notification->read_at ? __('Yes') : __('No') }}</td><td class="px-3 py-3 text-right">@unless($notification->read_at)<form method="POST" action="{{ route('admin.notifications.read', $notification) }}">@csrf @method('PATCH')<x-secondary-button type="submit">{{ __('Mark read') }}</x-secondary-button></form>@endunless</td></tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">{{ __('No notifications found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
