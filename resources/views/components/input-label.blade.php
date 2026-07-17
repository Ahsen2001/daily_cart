@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-brand-text']) }}>
    {{ $value ?? $slot }}
</label>
