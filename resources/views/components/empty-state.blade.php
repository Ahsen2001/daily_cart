@props([
    'title',
    'message' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'dc-empty-state']) }}>
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-brand-primary shadow-sm" aria-hidden="true">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-11A2.5 2.5 0 0 1 6.5 4Zm2.5 8h6" />
        </svg>
    </span>
    <h3 class="mt-4 text-lg font-extrabold text-brand-text">{{ $title }}</h3>
    @if ($message)
        <p class="mt-2 max-w-md text-sm leading-6 text-brand-muted">{{ $message }}</p>
    @endif
    @if ($action && $actionLabel)
        <a href="{{ $action }}" class="dc-button mt-5">{{ $actionLabel }}</a>
    @endif
</div>
