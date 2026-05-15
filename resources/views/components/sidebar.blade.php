@auth
    <aside class="hidden w-72 shrink-0 border-r border-green-100 bg-white/90 p-5 backdrop-blur lg:block">
        <div class="sticky top-24 space-y-6">
            <div class="rounded-3xl bg-brand-light p-4">
                <p class="text-xs font-semibold uppercase text-brand-dark">{{ Auth::user()->role?->name }}</p>
                <p class="mt-1 font-bold text-brand-text">{{ Auth::user()->name }}</p>
            </div>

            <nav class="space-y-2">
                <a class="dc-sidebar-link {{ request()->routeIs('dashboard') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="dc-sidebar-link {{ request()->routeIs('notifications.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('notifications.index') }}">{{ __('Notifications') }}</a>
                <a class="dc-sidebar-link {{ request()->routeIs('support.tickets.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('support.tickets.index') }}">{{ __('Support Tickets') }}</a>

                @if (Auth::user()->hasPrimaryRole('Admin', 'Super Admin'))
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.dashboard') }}">{{ __('Admin Dashboard') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.reports.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.reports.sales') }}">{{ __('Reports') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.subscriptions.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.subscriptions.index') }}">{{ __('Subscriptions') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.scheduled-orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.scheduled-orders.index') }}">{{ __('Scheduled Orders') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.products.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.products.index') }}">{{ __('Products') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.orders.index') }}">{{ __('Orders') }}</a>
                @endif

                @if (Auth::user()->hasPrimaryRole('Vendor'))
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.dashboard') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.dashboard') }}">{{ __('Vendor Dashboard') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.products.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.products.index') }}">{{ __('My Products') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.orders.index') }}">{{ __('Orders') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.subscriptions.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.subscriptions.index') }}">{{ __('Subscriptions') }}</a>
                @endif

                @if (Auth::user()->hasPrimaryRole('Rider'))
                    <a class="dc-sidebar-link {{ request()->routeIs('rider.dashboard') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('rider.dashboard') }}">{{ __('Rider Dashboard') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('rider.deliveries.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('rider.deliveries.index') }}">{{ __('Deliveries') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('rider.earnings.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('rider.earnings.index') }}">{{ __('Earnings') }}</a>
                @endif

                @if (Auth::user()->hasPrimaryRole('Customer'))
                    <a class="dc-sidebar-link {{ request()->routeIs('customer.products.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('customer.products.index') }}">{{ __('Shop Products') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('customer.cart.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('customer.cart.index') }}">{{ __('Cart') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('customer.orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('customer.orders.index') }}">{{ __('Orders') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('customer.subscriptions.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('customer.subscriptions.index') }}">{{ __('Subscriptions') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('customer.scheduled-orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('customer.scheduled-orders.index') }}">{{ __('Scheduled Orders') }}</a>
                @endif
            </nav>
        </div>
    </aside>
@endauth
