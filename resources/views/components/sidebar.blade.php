@auth
    <aside class="hidden w-72 shrink-0 border-r border-brand-border bg-white/90 p-5 backdrop-blur lg:sticky lg:top-[5.25rem] lg:block lg:h-[calc(100vh-5.25rem)] lg:overflow-hidden" aria-label="{{ __('Workspace navigation') }}">
        <div class="dc-scrollbar h-full space-y-5 overflow-y-auto pr-2">
            <div class="rounded-3xl border border-brand-border bg-brand-light p-4">
                <p class="dc-page-eyebrow">{{ Auth::user()->role?->name }}</p>
                <p class="mt-1 font-bold text-brand-text">{{ Auth::user()->name }}</p>
                <p class="mt-1 text-xs text-brand-muted">{{ __('Your DailyCart workspace') }}</p>
            </div>

            <nav class="space-y-1" aria-label="{{ __('Role navigation') }}">
                <a class="dc-sidebar-link {{ request()->routeIs('dashboard', 'super-admin.dashboard', 'admin.dashboard', 'vendor.dashboard', 'rider.dashboard', 'customer.dashboard') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="dc-sidebar-link {{ request()->routeIs('notifications.*', 'admin.notifications.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ Auth::user()->isAdminUser() ? route('admin.notifications.index') : route('notifications.index') }}">{{ __('Notifications') }}</a>
                <a class="dc-sidebar-link {{ request()->routeIs('support.tickets.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('support.tickets.index') }}">{{ __('Support Tickets') }}</a>

                @if (Auth::user()->hasPrimaryRole('Admin', 'Super Admin'))
                    @if (Auth::user()->isSuperAdmin())
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.admins.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.admins.index') }}">{{ __('Admin Accounts') }}</a>
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.roles.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.roles.index') }}">{{ __('Roles & Permissions') }}</a>
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.settings.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.settings.index') }}">{{ __('Platform Settings') }}</a>
                        <p class="px-3 pt-3 text-xs font-bold uppercase tracking-wider text-brand-muted">{{ __('Delivery Management') }}</p>
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.delivery.zones.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.delivery.zones.index') }}">{{ __('Delivery Zones') }}</a>
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.delivery.rules.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.delivery.rules.index') }}">{{ __('Delivery Fee Rules') }}</a>
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.delivery.simulator') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.delivery.simulator') }}">{{ __('Delivery Simulator') }}</a>
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.delivery.analytics') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.delivery.analytics') }}">{{ __('Delivery Analytics') }}</a>
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.delivery.history') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.delivery.history') }}">{{ __('Rule History') }}</a>
                    @endif
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.customers.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.customers.index') }}">{{ __('Customers') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.vendors.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.vendors.index') }}">{{ __('Vendors') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.reports.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.reports.sales') }}">{{ __('Reports') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.subscriptions.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.subscriptions.index') }}">{{ __('Subscriptions') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.scheduled-orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.scheduled-orders.index') }}">{{ __('Scheduled Orders') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.pages.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.pages.index') }}">{{ __('Pages') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.brands.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.brands.index') }}">{{ __('Brands') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.products.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.products.index') }}">{{ __('Products') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.orders.index') }}">{{ __('Orders') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.delivery-fees.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.delivery-fees.index') }}">{{ __('Delivery Fees') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.delivery-schedules.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.delivery-schedules.index') }}">{{ __('Delivery Schedules') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.contact-messages.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.contact-messages.index') }}">{{ __('Contact Messages') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('admin.newsletter.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('admin.newsletter.index') }}">{{ __('Newsletter') }}</a>
                    @if (Auth::user()->isSuperAdmin())
                        <a class="dc-sidebar-link {{ request()->routeIs('super-admin.maintenance.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('super-admin.maintenance.index') }}">{{ __('Backup & Restore') }}</a>
                    @endif
                @endif

                @if (Auth::user()->hasPrimaryRole('Vendor'))
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.products.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.products.index') }}">{{ __('My Products') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.orders.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.orders.index') }}">{{ __('Orders') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.promotions.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.promotions.index') }}">{{ __('Promotions & Offers') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.coupons.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.coupons.index') }}">{{ __('Coupons') }}</a>
                    <a class="dc-sidebar-link {{ request()->routeIs('vendor.subscriptions.*') ? 'dc-sidebar-link-active' : '' }}" href="{{ route('vendor.subscriptions.index') }}">{{ __('Subscriptions') }}</a>
                @endif

                @if (Auth::user()->hasPrimaryRole('Rider'))
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
