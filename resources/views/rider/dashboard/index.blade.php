@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="dc-page-eyebrow">{{ __('Delivery overview') }}</p><h2 class="dc-page-title">{{ __('Rider Dashboard') }}</h2></div>
            <a class="dc-button" href="{{ route('rider.reports.index') }}">{{ __('View reports') }}</a>
        </div>
    </x-slot>
    <div class="dc-page-section">
        <div class="dc-container grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($summary as $key => $value)
                <div class="dc-card border-l-4 border-l-brand-primary p-5">
                    <p class="dc-page-eyebrow">{{ __(str_replace('_', ' ', $key)) }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-brand-text">{{ str_contains($key, 'earnings') ? CurrencyService::formatLkr($value) : number_format($value) }}</p>
                </div>
            @endforeach
        </div>
        <div class="dc-container mt-6">
            <section class="dc-card flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="dc-page-eyebrow">{{ __('Customer rating') }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-brand-text">{{ number_format($ratingStatistics['average'], 1) }}<span class="text-base text-brand-muted">/5</span></p>
                    <p class="mt-1 text-sm text-brand-muted">{{ trans_choice(':count visible rating|:count visible ratings', $ratingStatistics['count'], ['count' => $ratingStatistics['count']]) }}</p>
                </div>
                <a class="dc-button" href="{{ route('rider.ratings.index') }}">{{ __('View customer feedback') }}</a>
            </section>
        </div>
    </div>
</x-app-layout>
