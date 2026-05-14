<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Rider Earnings') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-sm text-gray-500">{{ __('Today') }}</div>
                    <div class="mt-1 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($summary['daily']) }}</div>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-sm text-gray-500">{{ __('This Week') }}</div>
                    <div class="mt-1 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($summary['weekly']) }}</div>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-sm text-gray-500">{{ __('This Month') }}</div>
                    <div class="mt-1 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($summary['monthly']) }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
