<x-app-layout>
    <x-slot name="header">
        <div><p class="dc-page-eyebrow">{{ __('Your workspace') }}</p><h2 class="dc-page-title">{{ __('Dashboard') }}</h2></div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container">
            <div class="dc-panel">
                <p class="dc-page-eyebrow">{{ __('Welcome back') }}</p>
                <h3 class="mt-2 text-xl font-bold text-brand-text">{{ __('Your DailyCart account is ready.') }}</h3>
                <p class="mt-2 text-sm text-brand-muted">{{ __('Use the navigation to manage your shopping, orders, deliveries, or store operations.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
