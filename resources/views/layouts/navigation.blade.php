<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-green-100 bg-white/90 backdrop-blur-xl">
    <div class="dc-container">
        <div class="flex h-20 items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="transition duration-300 hover:scale-[1.02]">
                <x-application-logo />
            </a>

            <form method="GET" action="{{ Auth::user()->hasPrimaryRole('Customer') ? route('customer.products.index') : route('dashboard') }}" class="hidden max-w-xl flex-1 md:block">
                <x-search-bar name="search" placeholder="Search groceries, essentials, orders..." />
            </form>

            <div class="hidden items-center gap-3 lg:flex">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>

                @if (Auth::user()->hasPrimaryRole('Customer'))
                    <x-nav-link :href="route('customer.products.index')" :active="request()->routeIs('customer.products.*')">{{ __('Shop') }}</x-nav-link>
                    <x-nav-link :href="route('customer.cart.index')" :active="request()->routeIs('customer.cart.*')">{{ __('Cart') }}</x-nav-link>
                @endif

                @if (Auth::user()->isAdminUser())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">{{ __('Admin') }}</x-nav-link>
                @endif

                @if (Auth::user()->hasPrimaryRole('Vendor'))
                    <x-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">{{ __('Vendor') }}</x-nav-link>
                @endif

                @if (Auth::user()->hasPrimaryRole('Rider'))
                    <x-nav-link :href="route('rider.dashboard')" :active="request()->routeIs('rider.*')">{{ __('Rider') }}</x-nav-link>
                @endif

                <a href="{{ route('notifications.index') }}" class="relative rounded-full bg-brand-light px-4 py-2 text-sm font-semibold text-brand-dark transition hover:bg-brand-primary hover:text-white">
                    {{ __('Alerts') }}
                    <x-notification-badge class="absolute -right-2 -top-2">!</x-notification-badge>
                </a>
            </div>

            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 rounded-full border border-green-100 bg-white px-3 py-2 text-sm font-semibold text-brand-text shadow-sm transition hover:shadow-soft">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-primary text-white">{{ Str::of(Auth::user()->name)->substr(0, 1) }}</span>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-brand-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-2xl bg-brand-light p-3 text-brand-dark transition hover:bg-brand-primary hover:text-white lg:hidden">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-green-100 bg-white p-4 lg:hidden">
        <div class="mb-4">
            <form method="GET" action="{{ Auth::user()->hasPrimaryRole('Customer') ? route('customer.products.index') : route('dashboard') }}">
                <x-search-bar name="search" placeholder="Search DailyCart" />
            </form>
        </div>
        <div class="space-y-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">{{ __('Notifications') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('support.tickets.index')" :active="request()->routeIs('support.tickets.*')">{{ __('Support') }}</x-responsive-nav-link>

            @if (Auth::user()->hasPrimaryRole('Customer'))
                <x-responsive-nav-link :href="route('customer.products.index')" :active="request()->routeIs('customer.products.*')">{{ __('Products') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.cart.index')" :active="request()->routeIs('customer.cart.*')">{{ __('Cart') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.orders.index')" :active="request()->routeIs('customer.orders.*')">{{ __('Orders') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.subscriptions.index')" :active="request()->routeIs('customer.subscriptions.*')">{{ __('Subscriptions') }}</x-responsive-nav-link>
            @endif

            @if (Auth::user()->isAdminUser())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">{{ __('Admin Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.sales')" :active="request()->routeIs('admin.reports.*')">{{ __('Reports') }}</x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPrimaryRole('Vendor'))
                <x-responsive-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">{{ __('Vendor Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.products.index')" :active="request()->routeIs('vendor.products.*')">{{ __('My Products') }}</x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPrimaryRole('Rider'))
                <x-responsive-nav-link :href="route('rider.dashboard')" :active="request()->routeIs('rider.*')">{{ __('Rider Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rider.deliveries.index')" :active="request()->routeIs('rider.deliveries.*')">{{ __('Deliveries') }}</x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
            </form>
        </div>
    </div>
</nav>
