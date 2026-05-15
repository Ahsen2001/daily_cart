@props(['showText' => true])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <img src="{{ asset('images/logo.png') }}" alt="DailyCart logo" class="h-11 w-11 rounded-2xl object-contain shadow-sm">
    @if ($showText)
        <div class="leading-tight">
            <div class="text-lg font-extrabold text-brand-dark">DailyCart</div>
            <div class="text-xs font-medium text-brand-text/60">Daily essentials, delivered smart</div>
        </div>
    @endif
</div>
