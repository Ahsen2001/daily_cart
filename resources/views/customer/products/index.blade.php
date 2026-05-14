<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Products') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <form method="GET" class="grid gap-3 mb-6 sm:grid-cols-3">
                    <x-text-input name="search" placeholder="Search products" :value="request('search')" />
                    <select name="category_id" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Search') }}</x-primary-button>
                </form>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse ($products as $product)
                        <a href="{{ route('customer.products.show', $product) }}" class="block border border-gray-100 rounded-md p-4 hover:border-indigo-200">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="object-cover w-full h-40 rounded">
                            @endif
                            <div class="mt-3 font-semibold text-gray-900">{{ $product->name }}</div>
                            <div class="text-sm text-gray-600">{{ $product->category?->name }}</div>
                            <div class="mt-2 font-medium text-gray-900">{{ \App\Services\CurrencyService::formatLkr($product->discount_price ?? $product->price) }}</div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-600">{{ __('No approved products found.') }}</p>
                    @endforelse
                </div>

                <div class="mt-6">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
