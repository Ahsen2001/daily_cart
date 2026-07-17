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
                            <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Search') }}</x-primary-button>
                    @if (request()->hasAny(['search', 'category_id']))
                        <a href="{{ route('customer.products.index') }}" class="dc-button-secondary">{{ __('Reset') }}</a>
                    @endif
                </form>

                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @forelse ($products as $product)
                        <x-product-card :product="$product" />
                    @empty
                        <x-empty-state title="{{ __('No products found') }}" message="{{ __('Try a different search term or clear the category filter.') }}" :action="route('customer.products.index')" action-label="{{ __('Clear filters') }}" />
                    @endforelse
                </div>

                <div class="mt-6">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
