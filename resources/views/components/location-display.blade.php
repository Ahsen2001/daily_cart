@props([
    'label' => __('Registered location'),
    'address' => null,
    'latitude' => null,
    'longitude' => null,
    'compact' => false,
])

@php
    $hasCoordinates = filled($latitude) && filled($longitude);
    $mapsUrl = $hasCoordinates
        ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($latitude.','.$longitude)
        : null;
@endphp

<div {{ $attributes->class([
    'rounded-2xl border border-brand-border bg-brand-light/60',
    'p-3' => $compact,
    'p-4' => ! $compact,
]) }}>
    <div class="flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-dark shadow-sm">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-12a7 7 0 1 0-14 0c0 6.8 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold uppercase tracking-wide text-brand-muted">{{ $label }}</p>
            <p class="mt-1 break-words text-sm font-semibold text-brand-text">{{ $address ?: __('Address not provided') }}</p>
            @if ($hasCoordinates)
                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-1.5 text-sm font-bold text-brand-dark hover:underline">
                    {{ __('View on Google Maps') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5M19 5l-9 9M19 14v5H5V5h5"/></svg>
                </a>
            @else
                <p class="mt-2 text-xs font-medium text-amber-800">{{ __('No map pin has been saved.') }}</p>
            @endif
        </div>
    </div>
</div>
