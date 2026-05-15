@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Product Report') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Best-selling Products') }}</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @forelse ($best_selling as $row)
                            <div class="flex justify-between border-b py-2"><span>{{ $row->name }}</span><span>{{ number_format($row->sold_quantity) }} / {{ CurrencyService::formatLkr($row->revenue) }}</span></div>
                        @empty
                            <p class="text-gray-500">{{ __('No sales found.') }}</p>
                        @endforelse
                    </div>
                </section>
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Most Reviewed / Highest Rated') }}</h3>
                    <div class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            @foreach ($most_reviewed as $product)
                                <p class="border-b py-2">{{ $product->name }} <span class="text-gray-500">({{ $product->reviews_count }})</span></p>
                            @endforeach
                        </div>
                        <div>
                            @foreach ($highest_rated as $product)
                                <p class="border-b py-2">{{ $product->name }} <span class="text-gray-500">({{ number_format($product->reviews_avg_rating, 1) }}/5)</span></p>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-gray-500">{{ __('Most viewed products are a placeholder until product view tracking is enabled.') }}</p>
                </section>
            </div>

            @foreach (['low_stock' => 'Low Stock Products', 'out_of_stock' => 'Out-of-stock Products', 'near_expiry' => 'Expired / Near-expiry Products'] as $list => $title)
                <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="border-b px-6 py-4"><h3 class="font-semibold">{{ __($title) }}</h3></div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr><th class="px-4 py-3">{{ __('Product') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Category') }}</th><th>{{ __('Stock') }}</th><th>{{ __('Expiry') }}</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($$list as $product)
                                <tr><td class="px-4 py-3">{{ $product->name }}</td><td>{{ $product->vendor?->store_name }}</td><td>{{ $product->category?->name }}</td><td>{{ $product->stock_quantity }}</td><td>{{ $product->expiry_date?->format('Y-m-d') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $$list->links() }}</div>
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>
