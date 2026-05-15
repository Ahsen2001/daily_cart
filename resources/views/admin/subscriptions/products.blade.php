<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Subscription Eligible Products') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status')) <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div> @endif
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Product') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Category') }}</th><th>{{ __('Stock') }}</th><th>{{ __('Eligible') }}</th><th></th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($products as $product)
                            <tr>
                                <td class="px-4 py-3">{{ $product->name }}</td><td>{{ $product->vendor?->store_name }}</td><td>{{ $product->category?->name }}</td><td>{{ $product->stock_quantity }}</td><td>{{ $product->is_subscription_eligible ? __('Yes') : __('No') }}</td>
                                <td><form method="POST" action="{{ route('admin.subscriptions.products.update', $product) }}">@csrf @method('PATCH')<input type="hidden" name="is_subscription_eligible" value="{{ $product->is_subscription_eligible ? 0 : 1 }}"><button class="text-indigo-700 underline">{{ $product->is_subscription_eligible ? __('Disable') : __('Enable') }}</button></form></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
