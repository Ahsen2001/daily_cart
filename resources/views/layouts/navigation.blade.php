<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                        {{ __('Notifications') }}
                    </x-nav-link>
                    <x-nav-link :href="route('support.tickets.index')" :active="request()->routeIs('support.tickets.*')">
                        {{ __('Support') }}
                    </x-nav-link>

                    @if (Auth::user()->hasPrimaryRole('Super Admin'))
                        <x-nav-link :href="route('super-admin.dashboard')" :active="request()->routeIs('super-admin.*')">
                            {{ __('System') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPrimaryRole('Admin', 'Super Admin'))
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            {{ __('Admin') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">
                            {{ __('Products') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                            {{ __('Categories') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                            {{ __('Orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.deliveries.index')" :active="request()->routeIs('admin.deliveries.*')">
                            {{ __('Deliveries') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.finance.index')" :active="request()->routeIs('admin.finance.*')">
                            {{ __('Finance') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.refunds.index')" :active="request()->routeIs('admin.refunds.*')">
                            {{ __('Refunds') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.support-tickets.index')" :active="request()->routeIs('admin.support-tickets.*')">
                            {{ __('Tickets') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')">
                            {{ __('Reviews') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.coupons.index')" :active="request()->routeIs('admin.coupons.*')">
                            {{ __('Coupons') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.promotions.index')" :active="request()->routeIs('admin.promotions.*')">
                            {{ __('Promotions') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.advertisements.index')" :active="request()->routeIs('admin.advertisements.*')">
                            {{ __('Ads') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPrimaryRole('Vendor'))
                        <x-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">
                            {{ __('Vendor') }}
                        </x-nav-link>
                        <x-nav-link :href="route('vendor.products.index')" :active="request()->routeIs('vendor.products.*')">
                            {{ __('My Products') }}
                        </x-nav-link>
                        <x-nav-link :href="route('vendor.orders.index')" :active="request()->routeIs('vendor.orders.*')">
                            {{ __('Orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('vendor.earnings.index')" :active="request()->routeIs('vendor.earnings.*')">
                            {{ __('Earnings') }}
                        </x-nav-link>
                        <x-nav-link :href="route('vendor.reviews.index')" :active="request()->routeIs('vendor.reviews.*')">
                            {{ __('Reviews') }}
                        </x-nav-link>
                        <x-nav-link :href="route('vendor.coupons.index')" :active="request()->routeIs('vendor.coupons.*')">
                            {{ __('Coupons') }}
                        </x-nav-link>
                        <x-nav-link :href="route('vendor.promotions.index')" :active="request()->routeIs('vendor.promotions.*')">
                            {{ __('Promotions') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPrimaryRole('Rider'))
                        <x-nav-link :href="route('rider.dashboard')" :active="request()->routeIs('rider.*')">
                            {{ __('Rider') }}
                        </x-nav-link>
                        <x-nav-link :href="route('rider.deliveries.index')" :active="request()->routeIs('rider.deliveries.*')">
                            {{ __('Deliveries') }}
                        </x-nav-link>
                        <x-nav-link :href="route('rider.earnings.index')" :active="request()->routeIs('rider.earnings.*')">
                            {{ __('Earnings') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPrimaryRole('Customer'))
                        <x-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.*')">
                            {{ __('Shopping') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.products.index')" :active="request()->routeIs('customer.products.*')">
                            {{ __('Products') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.cart.index')" :active="request()->routeIs('customer.cart.*')">
                            {{ __('Cart') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.wishlist.index')" :active="request()->routeIs('customer.wishlist.*')">
                            {{ __('Wishlist') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.orders.index')" :active="request()->routeIs('customer.orders.*')">
                            {{ __('Orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.wallet.index')" :active="request()->routeIs('customer.wallet.*')">
                            {{ __('Wallet') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.refunds.index')" :active="request()->routeIs('customer.refunds.*')">
                            {{ __('Refunds') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.coupons.index')" :active="request()->routeIs('customer.coupons.*')">
                            {{ __('Coupons') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.loyalty.index')" :active="request()->routeIs('customer.loyalty.*')">
                            {{ __('Loyalty') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                {{ __('Notifications') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('support.tickets.index')" :active="request()->routeIs('support.tickets.*')">
                {{ __('Support') }}
            </x-responsive-nav-link>

            @if (Auth::user()->hasPrimaryRole('Super Admin'))
                <x-responsive-nav-link :href="route('super-admin.dashboard')" :active="request()->routeIs('super-admin.*')">
                    {{ __('System') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPrimaryRole('Admin', 'Super Admin'))
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                    {{ __('Admin') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">
                    {{ __('Products') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                    {{ __('Categories') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.deliveries.index')" :active="request()->routeIs('admin.deliveries.*')">
                    {{ __('Deliveries') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.finance.index')" :active="request()->routeIs('admin.finance.*')">
                    {{ __('Finance') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.refunds.index')" :active="request()->routeIs('admin.refunds.*')">
                    {{ __('Refunds') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.support-tickets.index')" :active="request()->routeIs('admin.support-tickets.*')">
                    {{ __('Tickets') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')">
                    {{ __('Reviews') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.coupons.index')" :active="request()->routeIs('admin.coupons.*')">
                    {{ __('Coupons') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.promotions.index')" :active="request()->routeIs('admin.promotions.*')">
                    {{ __('Promotions') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.advertisements.index')" :active="request()->routeIs('admin.advertisements.*')">
                    {{ __('Ads') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPrimaryRole('Vendor'))
                <x-responsive-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">
                    {{ __('Vendor') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.products.index')" :active="request()->routeIs('vendor.products.*')">
                    {{ __('My Products') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.orders.index')" :active="request()->routeIs('vendor.orders.*')">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.earnings.index')" :active="request()->routeIs('vendor.earnings.*')">
                    {{ __('Earnings') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.reviews.index')" :active="request()->routeIs('vendor.reviews.*')">
                    {{ __('Reviews') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.coupons.index')" :active="request()->routeIs('vendor.coupons.*')">
                    {{ __('Coupons') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.promotions.index')" :active="request()->routeIs('vendor.promotions.*')">
                    {{ __('Promotions') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPrimaryRole('Rider'))
                <x-responsive-nav-link :href="route('rider.dashboard')" :active="request()->routeIs('rider.*')">
                    {{ __('Rider') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rider.deliveries.index')" :active="request()->routeIs('rider.deliveries.*')">
                    {{ __('Deliveries') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rider.earnings.index')" :active="request()->routeIs('rider.earnings.*')">
                    {{ __('Earnings') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPrimaryRole('Customer'))
                <x-responsive-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.*')">
                    {{ __('Shopping') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.products.index')" :active="request()->routeIs('customer.products.*')">
                    {{ __('Products') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.cart.index')" :active="request()->routeIs('customer.cart.*')">
                    {{ __('Cart') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.wishlist.index')" :active="request()->routeIs('customer.wishlist.*')">
                    {{ __('Wishlist') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.orders.index')" :active="request()->routeIs('customer.orders.*')">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.wallet.index')" :active="request()->routeIs('customer.wallet.*')">
                    {{ __('Wallet') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.refunds.index')" :active="request()->routeIs('customer.refunds.*')">
                    {{ __('Refunds') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.coupons.index')" :active="request()->routeIs('customer.coupons.*')">
                    {{ __('Coupons') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.loyalty.index')" :active="request()->routeIs('customer.loyalty.*')">
                    {{ __('Loyalty') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
