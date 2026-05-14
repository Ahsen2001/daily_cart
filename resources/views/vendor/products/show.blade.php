<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $product->name }}</h2>
            <a href="{{ route('vendor.products.edit', $product) }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Edit') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="p-4 text-sm font-medium text-green-700 bg-white shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="p-6 bg-white shadow-sm lg:col-span-2 sm:rounded-lg">
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="font-medium text-gray-500">{{ __('Category') }}</dt><dd>{{ $product->category?->name }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Status') }}</dt><dd>{{ str_replace('_', ' ', ucfirst($product->status)) }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Price') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($product->price) }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Discount') }}</dt><dd>{{ $product->discount_price ? \App\Services\CurrencyService::formatLkr($product->discount_price) : '-' }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Stock') }}</dt><dd>{{ $product->stock_quantity }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Featured') }}</dt><dd>{{ $product->is_featured ? __('Yes') : __('No') }}</dd></div>
                    </dl>
                    <p class="mt-6 text-sm text-gray-700">{{ $product->description }}</p>
                </div>

                <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                    <form method="POST" action="{{ route('vendor.products.stock', $product) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <x-input-label for="stock_quantity" :value="__('Update Stock')" />
                        <x-text-input id="stock_quantity" name="stock_quantity" type="number" min="0" class="block w-full" :value="$product->stock_quantity" />
                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 font-semibold text-gray-900">{{ __('Images') }}</h3>
                <div class="grid gap-4 sm:grid-cols-4">
                    @foreach ($product->images as $image)
                        <div>
                            <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->alt_text }}" class="object-cover w-full h-32 rounded">
                            <form method="POST" action="{{ route('vendor.products.images.destroy', [$product, $image]) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <x-secondary-button>{{ __('Delete') }}</x-secondary-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 font-semibold text-gray-900">{{ __('Variants') }}</h3>
                <form method="POST" action="{{ route('vendor.products.variants.store', $product) }}" class="grid gap-3 mb-4 sm:grid-cols-4">
                    @csrf
                    <x-text-input name="name" placeholder="500g, 1kg, Small" required />
                    <x-text-input name="price" type="number" step="0.01" min="0" placeholder="Price" />
                    <x-text-input name="sku" placeholder="Variant SKU" />
                    <x-primary-button>{{ __('Add Variant') }}</x-primary-button>
                </form>

                <div class="space-y-2">
                    @foreach ($product->variants as $variant)
                        <div class="flex items-center justify-between border-b border-gray-100 py-2 text-sm">
                            <span>{{ $variant->name }} · {{ \App\Services\CurrencyService::formatLkr($variant->price) }}</span>
                            <form method="POST" action="{{ route('vendor.products.variants.destroy', [$product, $variant]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-700 underline">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
