<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="dc-page-eyebrow">Product catalogue</p>
                <h2 class="dc-page-title">Import Validation Preview</h2>
            </div>
            <a href="{{ route('vendor.products.import.create') }}" class="dc-button-secondary">Upload another file</a>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container mx-auto max-w-6xl space-y-6">
            @if ($preview['errors'] !== [])
                <section class="rounded-3xl border border-red-200 bg-red-50 p-6">
                    <h3 class="text-xl font-black text-red-900">Import needs corrections</h3>
                    <p class="mt-2 text-sm text-red-800">No products were imported. Correct every issue shown below, then upload the file again.</p>
                    <div class="mt-5 overflow-x-auto rounded-2xl bg-white">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-red-100 text-red-950">
                                <tr><th class="px-4 py-3 font-bold">Row</th><th class="px-4 py-3 font-bold">Issue</th></tr>
                            </thead>
                            <tbody class="divide-y divide-red-100">
                                @foreach ($preview['errors'] as $error)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ $error['row'] }}</td>
                                        <td class="px-4 py-3">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @else
                <section class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6">
                    <h3 class="text-xl font-black text-emerald-950">
                        Ready to import {{ count($preview['rows']) }} {{ \Illuminate\Support\Str::plural('product', count($preview['rows'])) }}
                    </h3>
                    <p class="mt-2 text-sm text-emerald-900">
                        SKU and product URL slug will be generated automatically. Images are uploaded separately after import.
                        Products with available stock will be submitted for normal admin approval.
                    </p>
                    <form method="POST" action="{{ route('vendor.products.import.confirm') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit" class="dc-button">Confirm import</button>
                    </form>
                </section>

                <section class="overflow-x-auto rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-700">
                            <tr>
                                <th class="px-4 py-3 font-bold">Row</th>
                                <th class="px-4 py-3 font-bold">Product</th>
                                <th class="px-4 py-3 font-bold">Category</th>
                                <th class="px-4 py-3 font-bold">Price</th>
                                <th class="px-4 py-3 font-bold">Stock</th>
                                <th class="px-4 py-3 font-bold">Approval</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($preview['rows'] as $row)
                                <tr>
                                    <td class="px-4 py-3 text-slate-500">{{ $row['row_number'] }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $row['name'] }}</td>
                                    <td class="px-4 py-3">{{ $row['category_name'] }}</td>
                                    <td class="px-4 py-3">{{ \App\Services\CurrencyService::formatLkr($row['price']) }}</td>
                                    <td class="px-4 py-3">{{ $row['stock_quantity'] }}</td>
                                    <td class="px-4 py-3">{{ $row['stock_quantity'] > 0 ? 'Pending admin approval' : 'Out of stock' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
