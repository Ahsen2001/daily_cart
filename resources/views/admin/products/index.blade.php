<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('All Products') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <form method="GET" class="grid gap-3 mb-6 sm:grid-cols-4">
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
                                <th class="px-3 py-2">{{ __('Vendor') }}</th>
                                <th class="px-3 py-2">{{ __('Price') }}</th>
                                <th class="px-3 py-2">{{ __('Stock') }}</th>
                                <th class="px-3 py-2">{{ __('Variants') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-3 py-3">{{ $product->vendor?->store_name }}</td>
                                    <td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($product->discount_price ?? $product->price) }}</td>
                                    <td class="px-3 py-3">{{ $product->stock_quantity }}</td>
                                    <td class="px-3 py-3">
                                        @if ($product->status === 'pending' && $product->variants_count > 0)
                                            <span class="dc-badge">{{ $product->variants_count }} {{ __('new') }}</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $product->variants_count }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($product->status)) }}</td>
                                    <td class="px-3 py-3 text-right"><a class="text-indigo-700 underline" href="{{ route('admin.products.show', $product) }}">{{ __('Review') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">{{ __('No products found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
