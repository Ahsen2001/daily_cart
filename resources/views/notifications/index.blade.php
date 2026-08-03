<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Notifications') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <div class="space-y-4">
                    @forelse ($notifications as $notification)
                        <div class="rounded-md border p-4 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-gray-900">{{ $notification->title }}</div>
                                    <div class="mt-1 text-sm text-gray-700">{{ $notification->message }}</div>
                                    @if ($notification->type === 'delivery_assigned' && filled($notification->data['email_details'] ?? null))
                                        <dl class="mt-4 grid gap-x-6 gap-y-2 rounded-lg bg-white/70 p-4 text-sm sm:grid-cols-2">
                                            @foreach ($notification->data['email_details'] as $label => $value)
                                                <div>
                                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</dt>
                                                    <dd class="mt-1 font-medium text-gray-900">{{ $value }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                        @if (filled($notification->data['email_action_url'] ?? null))
                                            <a href="{{ $notification->data['email_action_url'] }}" class="mt-4 inline-flex items-center rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                                                {{ $notification->data['email_action_label'] ?? 'Open delivery' }}
                                            </a>
                                        @endif
                                    @endif
                                    <div class="mt-2 text-xs text-gray-500"><x-local-time :date="$notification->created_at" /></div>
                                </div>
                                <form class="flex-shrink-0" method="POST" action="{{ $notification->read_at ? route('notifications.unread', $notification) : route('notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-secondary-button type="submit" class="whitespace-nowrap">{{ $notification->read_at ? __('Mark unread') : __('Mark read') }}</x-secondary-button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">{{ __('No notifications found.') }}</div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
