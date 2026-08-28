@php
    $selectedRating = (int) old('rating', $riderRating?->rating);
    $selectedTags = old('tags', $riderRating?->tags ?? []);
    $tagLabels = [
        'on_time' => __('On time'), 'friendly' => __('Friendly'),
        'careful_handling' => __('Handled items carefully'), 'good_communication' => __('Good communication'),
        'late_delivery' => __('Late delivery'), 'poor_communication' => __('Poor communication'),
    ];
@endphp

<x-app-layout>
    <x-slot name="header"><div><p class="dc-page-eyebrow">{{ __('Delivery feedback') }}</p><h2 class="dc-page-title">{{ $riderRating ? __('Update rider rating') : __('Rate your rider') }}</h2></div></x-slot>
    <div class="dc-page-section">
        <div class="dc-container max-w-3xl">
            <div class="dc-card p-6 sm:p-8">
                <div class="flex items-center gap-4 rounded-2xl bg-brand-light p-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-primary text-lg font-bold text-white">{{ strtoupper(substr($order->delivery->rider->user?->name ?? 'R', 0, 1)) }}</div>
                    <div><p class="font-extrabold text-brand-text">{{ $order->delivery->rider->user?->name }}</p><p class="text-sm text-brand-muted">{{ __('Order') }} {{ $order->order_number }}</p></div>
                </div>
                <form method="POST" action="{{ route('customer.rider-ratings.store', $order) }}" class="mt-6 space-y-6">
                    @csrf
                    <fieldset>
                        <legend class="text-sm font-bold text-brand-text">{{ __('Overall delivery rating') }}</legend>
                        <div class="mt-3 grid grid-cols-5 gap-2">
                            @foreach ([1 => 'Very poor', 2 => 'Poor', 3 => 'Average', 4 => 'Good', 5 => 'Excellent'] as $score => $label)
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="radio" name="rating" value="{{ $score }}" @checked($selectedRating === $score) required>
                                <span class="flex min-h-20 flex-col items-center justify-center rounded-2xl border border-brand-border bg-white text-center transition peer-checked:border-brand-primary peer-checked:bg-green-50 peer-checked:text-brand-dark"><span class="text-2xl text-amber-400">★</span><span class="mt-1 text-xs font-bold">{{ $score }}</span></span>
                            </label>
                            @endforeach
                        </div>
                        @error('rating')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </fieldset>
                    <fieldset>
                        <legend class="text-sm font-bold text-brand-text">{{ __('What stood out?') }} <span class="font-normal text-brand-muted">{{ __('(optional)') }}</span></legend>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($tagLabels as $value => $label)
                            <label class="cursor-pointer"><input class="peer sr-only" type="checkbox" name="tags[]" value="{{ $value }}" @checked(in_array($value, $selectedTags, true))><span class="inline-flex rounded-full border border-brand-border px-4 py-2 text-sm font-semibold transition peer-checked:border-brand-primary peer-checked:bg-brand-primary peer-checked:text-white">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div>
                        <x-input-label for="rider-rating-comment" :value="__('Comment (optional)')" />
                        <textarea id="rider-rating-comment" name="comment" rows="5" maxlength="2000" class="mt-2 w-full rounded-2xl border-brand-border" placeholder="{{ __('Tell us about the delivery experience only.') }}">{{ old('comment', $riderRating?->comment) }}</textarea>
                        @error('comment')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <a class="dc-button-secondary justify-center" href="{{ route('customer.orders.show', $order) }}">{{ __('Back to order') }}</a>
                        <button class="dc-button justify-center" type="submit">{{ $riderRating ? __('Update rating') : __('Submit rating') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
