@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Rider Dashboard') }}</h2>
            <x-application-logo :show-text="false" />
            <a class="rounded bg-indigo-600 px-3 py-2 text-sm text-white" href="{{ route('rider.reports.index') }}">{{ __('View reports') }}</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto grid gap-4 max-w-7xl sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ($summary as $key => $value)
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                    <p class="mt-2 text-xl font-bold">{{ str_contains($key, 'earnings') ? CurrencyService::formatLkr($value) : number_format($value) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
