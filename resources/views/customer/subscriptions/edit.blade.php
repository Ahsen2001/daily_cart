<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Edit Subscription') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form class="space-y-5 rounded-lg bg-white p-6 shadow-sm" method="POST" action="{{ route('customer.subscriptions.update', $subscription) }}">
                @csrf @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="text-sm font-medium">{{ __('Quantity') }}</label><input class="mt-1 w-full rounded border-gray-300" type="number" min="1" name="quantity" value="{{ old('quantity', $subscription->quantity) }}"></div>
                    <div><label class="text-sm font-medium">{{ __('Preferred Delivery Time') }}</label><input class="mt-1 w-full rounded border-gray-300" type="time" name="preferred_delivery_time" value="{{ old('preferred_delivery_time', $subscription->preferred_delivery_time) }}"></div>
                    <div><label class="text-sm font-medium">{{ __('End Date') }}</label><input class="mt-1 w-full rounded border-gray-300" type="date" name="end_date" value="{{ old('end_date', $subscription->end_date?->format('Y-m-d')) }}"></div>
                    <div><label class="text-sm font-medium">{{ __('Payment Method') }}</label><select class="mt-1 w-full rounded border-gray-300" name="payment_method">@foreach (['cash_on_delivery','card','bank_transfer','wallet'] as $method)<option value="{{ $method }}" @selected(old('payment_method', $subscription->payment_method) === $method)>{{ __(str_replace('_', ' ', $method)) }}</option>@endforeach</select></div>
                </div>
                <div><label class="text-sm font-medium">{{ __('Delivery Address') }}</label><textarea class="mt-1 w-full rounded border-gray-300" name="delivery_address" rows="3">{{ old('delivery_address', $subscription->delivery_address) }}</textarea></div>
                <div><label class="text-sm font-medium">{{ __('Notes') }}</label><textarea class="mt-1 w-full rounded border-gray-300" name="notes" rows="2">{{ old('notes', $subscription->notes) }}</textarea></div>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Update Subscription') }}</button>
            </form>
        </div>
    </div>
</x-app-layout>
