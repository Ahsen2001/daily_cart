@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full bg-brand-primary px-4 py-2 text-sm font-semibold leading-5 text-white shadow-sm transition duration-200 hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2'
            : 'inline-flex items-center rounded-full px-4 py-2 text-sm font-medium leading-5 text-brand-text/70 transition duration-200 hover:bg-brand-light hover:text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
