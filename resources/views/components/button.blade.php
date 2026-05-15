@props(['variant' => 'primary', 'type' => 'button'])

@php
    $classes = $variant === 'secondary' ? 'dc-button-secondary' : 'dc-button';
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
