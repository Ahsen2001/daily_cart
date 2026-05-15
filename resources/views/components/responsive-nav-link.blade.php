@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-2xl bg-brand-primary px-4 py-3 text-start text-base font-semibold text-white shadow-sm transition duration-200'
            : 'block w-full rounded-2xl px-4 py-3 text-start text-base font-medium text-brand-text/70 transition duration-200 hover:bg-brand-light hover:text-brand-dark';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
