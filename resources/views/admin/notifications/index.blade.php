<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('All Notifications') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('User') }}</th><th class="px-3 py-2">{{ __('Title') }}</th><th class="px-3 py-2">{{ __('Type') }}</th><th class="px-3 py-2">{{ __('Read') }}</th><th class="px-3 py-2"></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($notifications as $notification)
                                <tr><td class="px-3 py-3">{{ $notification->user?->name }}</td><td class="px-3 py-3">{{ $notification->title }}</td><td class="px-3 py-3">{{ $notification->type }}</td><td class="px-3 py-3">{{ $notification->read_at ? __('Yes') : __('No') }}</td><td class="px-3 py-3 text-right">@unless($notification->read_at)<form method="POST" action="{{ route('admin.notifications.read', $notification) }}">@csrf @method('PATCH')<x-secondary-button>{{ __('Mark read') }}</x-secondary-button></form>@endunless</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No notifications found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
