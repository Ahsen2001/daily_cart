<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ __('Database Backup & System Restore') }}</h2></x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))<div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>@endif
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">{{ __('Database Backup') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ __('Creates a JSON snapshot of application tables, excluding cache and sessions.') }}</p>
                    <form method="POST" action="{{ route('super-admin.maintenance.backup') }}" class="mt-5">@csrf<x-primary-button>{{ __('Create Backup') }}</x-primary-button></form>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">{{ __('System Restore') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ __('Available backups are listed below for download and manual restore review. Live destructive restore is intentionally not automatic from the dashboard.') }}</p>
                    <form method="POST" action="{{ route('super-admin.maintenance.clear-compiled') }}" class="mt-5">@csrf<x-secondary-button type="submit">{{ __('Clear Compiled Cache') }}</x-secondary-button></form>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">{{ __('Available Backups') }}</h3>
                <div class="mt-4 divide-y text-sm">
                    @forelse ($backups as $backup)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="font-semibold">{{ $backup['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($backup['size'] / 1024, 1) }} KB · <x-local-time :date="\Carbon\Carbon::createFromTimestamp($backup['modified'])" /></p>
                            </div>
                            <a class="text-indigo-700 underline" href="{{ route('super-admin.maintenance.download', $backup['name']) }}">{{ __('Download') }}</a>
                        </div>
                    @empty
                        <p class="py-4 text-gray-500">{{ __('No backups created yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
