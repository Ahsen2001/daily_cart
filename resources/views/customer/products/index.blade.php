<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-brand-dark">{{ __('DailyCart Marketplace') }}</p>
                <h2 class="text-2xl font-extrabold leading-tight text-brand-text">{{ __('Products') }}</h2>
            </div>
            <x-notification-badge>{{ __('LKR only') }}</x-notification-badge>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container">
            <div class="dc-panel">
                <form method="GET" class="dc-filter-bar mb-8 lg:grid-cols-[1fr_260px_auto_auto]" role="search">
                    <x-search-bar name="search" placeholder="Search products, brands, essentials..." :value="request('search')" />
                    <select name="category_id" aria-label="{{ __('Filter by category') }}">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('category_id')===$category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Search') }}</x-primary-button>
                    @if (request()->hasAny(['search', 'category_id']))
                    <a href="{{ route('customer.products.index') }}" class="dc-button-secondary">{{ __('Reset') }}</a>
                    @endif
                </form>

                @if ($products->isNotEmpty())
                <form id="bulk-cart-form" method="POST" action="{{ route('customer.cart.bulk') }}" class="mb-6 flex flex-col gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                    @csrf
                    <div>
                        <p class="font-bold text-brand-text">{{ __('Build your cart') }}</p>
                        <p class="text-sm text-brand-text/70">{{ __('Select multiple products below, choose quantities, then add them together.') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="bulk-selection-count" class="text-sm font-semibold text-brand-text/70">{{ __('0 selected') }}</span>
                        <button id="bulk-add-button" type="submit" disabled class="dc-button whitespace-nowrap opacity-50 disabled:cursor-not-allowed">{{ __('Add selected to cart') }}</button>
                    </div>
                </form>
                @endif

                <div class="grid grid-cols-2 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                    @forelse ($products as $product)
                    <x-product-card :product="$product" :bulk-selectable="true" />
                    @empty
                    <x-empty-state title="{{ __('No products found') }}" message="{{ __('Try a different search term or clear the category filter.') }}" :action="route('customer.products.index')" action-label="{{ __('Clear filters') }}" />
                    @endforelse
                </div>

                <div class="mt-6">{{ $products->links() }}</div>
            </div>
        </div>
    </div>

    @if ($products->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = [...document.querySelectorAll('[data-bulk-product]')];
            const count = document.getElementById('bulk-selection-count');
            const button = document.getElementById('bulk-add-button');
            const update = () => {
                const selected = inputs.filter((input) => input.checked).length;
                count.textContent = `${selected} selected`;
                button.disabled = selected === 0;
                button.classList.toggle('opacity-50', selected === 0);
            };
            inputs.forEach((input) => input.addEventListener('change', update));
            update();
        });
    </script>
    @endif
</x-app-layout>