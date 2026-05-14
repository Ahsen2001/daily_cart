<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Product Review') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="p-4 text-sm font-medium text-green-700 bg-white shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $product->vendor?->store_name }} · {{ $product->category?->name }}</p>
                        <p class="mt-4 text-sm text-gray-700">{{ $product->description }}</p>
                    </div>
                    <div class="text-sm text-gray-700">
                        <div>{{ __('Price') }}: {{ \App\Services\CurrencyService::formatLkr($product->price) }}</div>
                        <div>{{ __('Discount') }}: {{ $product->discount_price ? \App\Services\CurrencyService::formatLkr($product->discount_price) : '-' }}</div>
                        <div>{{ __('Stock') }}: {{ $product->stock_quantity }}</div>
                        <div>{{ __('Status') }}: {{ str_replace('_', ' ', ucfirst($product->status)) }}</div>
                        <div>{{ __('Featured') }}: {{ $product->is_featured ? __('Yes') : __('No') }}</div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-6">
                    <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                        @csrf
                        @method('PATCH')
                        <x-primary-button>{{ __('Approve') }}</x-primary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.products.reject', $product) }}">
                        @csrf
                        @method('PATCH')
                        <x-secondary-button>{{ __('Reject') }}</x-secondary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.products.feature', $product) }}">
                        @csrf
                        @method('PATCH')
                        <x-secondary-button>{{ $product->is_featured ? __('Unfeature') : __('Feature') }}</x-secondary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.products.status', $product) }}" class="flex gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="border-gray-300 rounded-md shadow-sm">
                            @foreach (['pending', 'approved', 'rejected', 'inactive', 'out_of_stock'] as $status)
                                <option value="{{ $status }}" @selected($product->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                        <x-secondary-button>{{ __('Update Status') }}</x-secondary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
