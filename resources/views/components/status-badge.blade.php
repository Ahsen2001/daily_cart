@props(['status'])

@php
    $normalized = strtolower(str_replace(' ', '_', (string) $status));
    $tone = match (true) {
        in_array($normalized, ['active', 'approved', 'available', 'completed', 'confirmed', 'delivered', 'paid', 'published', 'verified'], true) => 'success',
        in_array($normalized, ['pending', 'assigned', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'on_the_way'], true) => 'warning',
        in_array($normalized, ['cancelled', 'failed', 'inactive', 'out_of_stock', 'rejected', 'suspended'], true) => 'danger',
        default => 'neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => 'dc-status dc-status-'.$tone]) }}>
    {{ __(str_replace('_', ' ', $normalized)) }}
</span>
