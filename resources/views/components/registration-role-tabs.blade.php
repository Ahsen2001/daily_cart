@props(['active' => 'customer'])

@php
    $roles = [
        'customer' => [
            'label' => __('Customer'),
            'description' => __('Shop and schedule deliveries'),
            'route' => route('register'),
            'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM9 19a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z',
        ],
        'vendor' => [
            'label' => __('Vendor'),
            'description' => __('Sell products and manage a store'),
            'route' => route('vendor.register'),
            'icon' => 'M3 10h18M5 10v10h14V10M4 4h16l1 6H3l1-6Zm5 10h6v6H9v-6Z',
        ],
        'rider' => [
            'label' => __('Rider'),
            'description' => __('Deliver orders and track earnings'),
            'route' => route('rider.register'),
            'icon' => 'M5 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm14 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM7 19h7l2-5h3l2 3M10 9h4l2 5M8 9h2l-1-3H6',
        ],
    ];
@endphp

<div class="mb-7">
    <p class="dc-page-eyebrow">{{ __('Choose your account') }}</p>
    <div class="mt-3 grid grid-cols-3 gap-2" aria-label="{{ __('Registration type') }}">
        @foreach ($roles as $key => $role)
            <a
                href="{{ $role['route'] }}"
                @class([
                    'group flex min-h-20 flex-col items-center justify-center gap-2 rounded-2xl border p-2.5 text-center transition duration-200 sm:min-h-24 sm:flex-row sm:items-start sm:justify-start sm:gap-3 sm:p-3.5 sm:text-left',
                    'border-brand-primary bg-brand-light shadow-sm ring-1 ring-brand-primary/10' => $active === $key,
                    'border-brand-border bg-white hover:-translate-y-0.5 hover:border-brand-primary/50 hover:shadow-card' => $active !== $key,
                ])
                @if ($active === $key) aria-current="page" @endif
            >
                <span @class([
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                    'bg-brand-primary text-white' => $active === $key,
                    'bg-brand-light text-brand-dark group-hover:bg-brand-primary group-hover:text-white' => $active !== $key,
                ])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $role['icon'] }}" /></svg>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-extrabold text-brand-text">{{ $role['label'] }}</span>
                    <span class="mt-1 hidden text-[11px] leading-4 text-brand-muted sm:block">{{ $role['description'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
