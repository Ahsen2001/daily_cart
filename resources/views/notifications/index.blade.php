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
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $notification->title }}</div>
                                    <div class="mt-1 text-sm text-gray-700">{{ $notification->message }}</div>
                                    <div class="mt-2 text-xs text-gray-500">{{ $notification->created_at->format('M d, Y h:i A') }}</div>
                                </div>
                                <form method="POST" action="{{ $notification->read_at ? route('notifications.unread', $notification) : route('notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-secondary-button>{{ $notification->read_at ? __('Mark unread') : __('Mark read') }}</x-secondary-button>
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
