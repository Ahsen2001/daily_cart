@props(['title', 'value' => null, 'accent' => false])

<div {{ $attributes->merge(['class' => 'dc-card animate-fade-up']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-brand-text/60">{{ $title }}</p>
            <div class="mt-2 text-2xl font-bold text-brand-text">{{ $value ?? $slot }}</div>
        </div>
        <div class="rounded-2xl {{ $accent ? 'bg-brand-orange/10 text-brand-orange' : 'bg-brand-light text-brand-dark' }} p-3">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
            </svg>
        </div>
    </div>
</div>
