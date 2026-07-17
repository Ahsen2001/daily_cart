<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><p class="dc-page-eyebrow">{{ __('Catalog') }}</p><h2 class="dc-page-title">{{ __('My Products') }}</h2></div>
            <a href="{{ route('vendor.products.create') }}" class="dc-button">{{ __('Add Product') }}</a>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container">
            <div class="dc-panel">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <form method="GET" class="dc-filter-bar mb-6 sm:grid-cols-4">
                    <x-text-input name="search" placeholder="Search products" :value="request('search')" />
                    <select name="category_id" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['pending', 'approved', 'rejected', 'inactive', 'out_of_stock'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase text-gray-500">
                                <th class="px-3 py-2">{{ __('Product') }}</th>
                                <th class="px-3 py-2">{{ __('Category') }}</th>
                                <th class="px-3 py-2">{{ __('Price') }}</th>
                                <th class="px-3 py-2">{{ __('Stock') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-3 py-3">{{ $product->category?->name }}</td>
                                    <td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($product->discount_price ?? $product->price) }}</td>
                                    <td class="px-3 py-3">{{ $product->stock_quantity }}</td>
                                    <td class="px-3 py-3"><x-status-badge :status="$product->status" /></td>
                                    <td class="px-3 py-3 text-right">
                                        <a class="font-bold text-brand-dark hover:underline" href="{{ route('vendor.products.show', $product) }}">{{ __('View') }}</a>
                                        <a class="ml-3 font-bold text-brand-dark hover:underline" href="{{ route('vendor.products.edit', $product) }}">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">{{ __('No products found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
