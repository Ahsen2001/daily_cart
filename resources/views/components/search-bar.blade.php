@props(['placeholder' => 'Search DailyCart'])

<label class="dc-search">
    <svg class="h-5 w-5 text-brand-dark/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
    </svg>
    <input {{ $attributes->merge(['class' => 'w-full border-0 bg-transparent p-0 text-sm text-brand-text placeholder:text-brand-text/45 focus:ring-0', 'placeholder' => $placeholder]) }}>
</label>
