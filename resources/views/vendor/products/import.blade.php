<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="dc-page-eyebrow">Product catalogue</p>
                <h2 class="dc-page-title">Bulk Product Import</h2>
            </div>
            <a href="{{ route('vendor.products.index') }}" class="dc-button-secondary">Back to products</a>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container mx-auto max-w-4xl space-y-6">
            <section class="dc-panel">
                <h3 class="text-xl font-black text-slate-900">Start with a DailyCart template</h3>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Download a template, add up to 250 products, then upload it for validation. DailyCart generates each
                    SKU and product URL slug. Images are added separately after the import.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('vendor.products.import.template', 'csv') }}" class="dc-button-secondary">Download CSV template</a>
                    <a href="{{ route('vendor.products.import.template', 'xlsx') }}" class="dc-button-secondary">Download Excel template</a>
                </div>
            </section>

            <section class="dc-panel">
                <h3 class="text-xl font-black text-slate-900">Upload and validate</h3>
                <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-slate-600">
                    <li>Use an active category name or category slug.</li>
                    <li>Maximum file size: 5 MB. Formulas are rejected for security.</li>
                    <li>Imported products follow the normal admin approval process.</li>
                </ul>

                <form method="POST" action="{{ route('vendor.products.import.preview') }}" enctype="multipart/form-data" class="mt-6">
                    @csrf
                    <label for="import_file" class="block text-sm font-bold text-slate-800">CSV or Excel file</label>
                    <input id="import_file" name="import_file" type="file"
                        accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required
                        class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                    @error('import_file')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="dc-button mt-5">Validate import</button>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
