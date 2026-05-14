<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Wishlist') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <div class="space-y-4">
                    @forelse ($wishlists as $wishlist)
                        <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $wishlist->product->name }}</div>
                                <div class="text-sm text-gray-600">{{ $wishlist->product->category?->name }} · {{ \App\Services\CurrencyService::formatLkr($wishlist->product->discount_price ?? $wishlist->product->price) }}</div>
                            </div>

                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('customer.wishlist.move-to-cart', $wishlist) }}">
                                    @csrf
                                    <x-primary-button>{{ __('Move to Cart') }}</x-primary-button>
                                </form>

                                <form method="POST" action="{{ route('customer.wishlist.destroy', $wishlist) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-secondary-button>{{ __('Remove') }}</x-secondary-button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">{{ __('Your wishlist is empty.') }}</p>
                    @endforelse
                </div>

                <div class="mt-6">{{ $wishlists->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
