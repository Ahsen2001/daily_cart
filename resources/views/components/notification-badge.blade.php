@props(['count' => null])

<span {{ $attributes->merge(['class' => 'dc-badge']) }}>
    {{ $count ?? $slot }}
</span>
