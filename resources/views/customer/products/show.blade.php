<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $product->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-6 bg-white p-6 shadow-sm sm:rounded-lg lg:grid-cols-2">
                <div>
                    @if ($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="object-cover w-full rounded-md max-h-96">
                    @endif

                    <div class="grid grid-cols-3 gap-3 mt-4">
                        @foreach ($product->images as $image)
                            <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->alt_text }}" class="object-cover w-full h-24 rounded">
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-600">{{ $product->category?->name }} · {{ $product->vendor?->store_name }}</div>
                    <h3 class="mt-2 text-2xl font-semibold text-gray-900">{{ $product->name }}</h3>
                    <div class="mt-4 text-xl font-semibold text-gray-900">{{ \App\Services\CurrencyService::formatLkr($product->discount_price ?? $product->price) }}</div>
                    @if ($product->discount_price)
                        <div class="text-sm text-gray-500 line-through">{{ \App\Services\CurrencyService::formatLkr($product->price) }}</div>
                    @endif
                    <p class="mt-6 text-sm text-gray-700">{{ $product->description }}</p>

                    <dl class="grid gap-3 mt-6 text-sm sm:grid-cols-2">
                        <div><dt class="font-medium text-gray-500">{{ __('Brand') }}</dt><dd>{{ $product->brand ?: '-' }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Unit') }}</dt><dd>{{ $product->unit_type }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Weight') }}</dt><dd>{{ $product->weight ?: '-' }}</dd></div>
                        <div><dt class="font-medium text-gray-500">{{ __('Stock') }}</dt><dd>{{ $product->stock_quantity }}</dd></div>
                    </dl>

                    @if ($product->variants->isNotEmpty())
                        <div class="mt-6">
                            <h4 class="font-medium text-gray-900">{{ __('Variants') }}</h4>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($product->variants as $variant)
                                    <span class="rounded border border-gray-200 px-3 py-1 text-sm">{{ $variant->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
