<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-brand-dark">{{ __('Welcome to DailyCart') }}</p>
                <h2 class="text-2xl font-extrabold leading-tight text-brand-text">{{ __('Customer Dashboard') }}</h2>
            </div>
            <x-application-logo :show-text="false" />
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container space-y-6">
            <div class="rounded-[2rem] bg-white p-6 shadow-soft">
                <div class="grid gap-6 lg:grid-cols-[1fr_380px] lg:items-center">
                    <div>
                        <x-notification-badge>{{ __('Fresh Green Shopping') }}</x-notification-badge>
                        <h3 class="mt-4 text-3xl font-extrabold">{{ __('Order daily essentials with a clean, smart experience.') }}</h3>
                        <p class="mt-3 text-brand-text/70">{{ __('Browse approved products, save favorites, schedule deliveries, and track every order from your dashboard.') }}</p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a class="dc-button" href="{{ route('customer.products.index') }}">{{ __('Browse Products') }}</a>
                            <a class="dc-button-secondary" href="{{ route('customer.cart.index') }}">{{ __('View Cart') }}</a>
                            <a class="dc-button-secondary" href="{{ route('customer.wishlist.index') }}">{{ __('Wishlist') }}</a>
                        </div>
                    </div>
                    <div class="rounded-3xl bg-brand-light p-6">
                        <x-application-logo />
                        <div class="mt-6 grid gap-3">
                            <a class="dc-sidebar-link" href="{{ route('customer.subscriptions.index') }}">{{ __('Manage Subscriptions') }}</a>
                            <a class="dc-sidebar-link" href="{{ route('customer.scheduled-orders.index') }}">{{ __('Scheduled Future Orders') }}</a>
                            <a class="dc-sidebar-link" href="{{ route('customer.loyalty.index') }}">{{ __('Loyalty Points') }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dc-dashboard-grid">
                <x-dashboard-card title="Products" value="Browse" />
                <x-dashboard-card title="Cart" value="Checkout" />
                <x-dashboard-card title="Subscriptions" value="Repeat" />
                <x-dashboard-card title="Support" value="Help" accent />
            </div>
        </div>
    </div>
</x-app-layout>
