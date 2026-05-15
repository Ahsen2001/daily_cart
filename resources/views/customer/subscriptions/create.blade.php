@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Create Subscription') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form class="space-y-5 rounded-lg bg-white p-6 shadow-sm" method="POST" action="{{ route('customer.subscriptions.store') }}">
                @csrf
                <div>
                    <label class="text-sm font-medium">{{ __('Product') }}</label>
                    <select class="mt-1 w-full rounded border-gray-300" name="product_id" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} - {{ CurrencyService::formatLkr($product->discount_price ?: $product->price) }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="text-sm font-medium">{{ __('Frequency') }}</label><select class="mt-1 w-full rounded border-gray-300" name="frequency"><option value="daily">{{ __('Daily') }}</option><option value="weekly">{{ __('Weekly') }}</option><option value="monthly">{{ __('Monthly') }}</option></select></div>
                    <div><label class="text-sm font-medium">{{ __('Quantity') }}</label><input class="mt-1 w-full rounded border-gray-300" type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}"></div>
                    <div><label class="text-sm font-medium">{{ __('Start Date') }}</label><input class="mt-1 w-full rounded border-gray-300" type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}"></div>
                    <div><label class="text-sm font-medium">{{ __('End Date') }}</label><input class="mt-1 w-full rounded border-gray-300" type="date" name="end_date" value="{{ old('end_date') }}"></div>
                    <div><label class="text-sm font-medium">{{ __('Preferred Delivery Time') }}</label><input class="mt-1 w-full rounded border-gray-300" type="time" name="preferred_delivery_time" value="{{ old('preferred_delivery_time', '08:00') }}"></div>
                    <div><label class="text-sm font-medium">{{ __('Payment Method') }}</label><select class="mt-1 w-full rounded border-gray-300" name="payment_method"><option value="cash_on_delivery">{{ __('Cash on Delivery') }}</option><option value="card">{{ __('Card placeholder') }}</option><option value="bank_transfer">{{ __('Bank Transfer placeholder') }}</option><option value="wallet">{{ __('Wallet placeholder') }}</option></select></div>
                </div>
                <div><label class="text-sm font-medium">{{ __('Delivery Address') }}</label><textarea class="mt-1 w-full rounded border-gray-300" name="delivery_address" rows="3">{{ old('delivery_address') }}</textarea></div>
                <div><label class="text-sm font-medium">{{ __('Notes') }}</label><textarea class="mt-1 w-full rounded border-gray-300" name="notes" rows="2">{{ old('notes') }}</textarea></div>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Save Subscription') }}</button>
            </form>
        </div>
    </div>
</x-app-layout>
