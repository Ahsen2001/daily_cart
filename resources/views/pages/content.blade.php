@php
    $title = $content["page_{$page}_title"];
    $subtitle = $content["page_{$page}_subtitle"];
    $body = $content["page_{$page}_body"];
    $email = $content["page_{$page}_email"];
    $phone = $content["page_{$page}_phone"];
    $address = $content["page_{$page}_address"];
    $ctaLabel = $content["page_{$page}_cta_label"];
    $ctaUrl = $content["page_{$page}_cta_url"];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | DailyCart</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-brand-light font-sans text-brand-text antialiased">
        <header class="sticky top-0 z-40 border-b border-green-100 bg-white/90 backdrop-blur-xl">
            <div class="dc-container flex h-20 items-center justify-between">
                <a href="{{ url('/') }}" class="transition hover:scale-[1.02]"><x-application-logo /></a>
                <nav class="hidden items-center gap-3 md:flex">
                    <a class="dc-button-secondary" href="{{ route('pages.about') }}">{{ __('About') }}</a>
                    <a class="dc-button-secondary" href="{{ route('pages.offers') }}">{{ __('Offers') }}</a>
                    <a class="dc-button-secondary" href="{{ route('pages.contact') }}">{{ __('Contact') }}</a>
                    @auth
                        <a class="dc-button" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                    @else
                        <a class="dc-button" href="{{ route('login') }}">{{ __('Login') }}</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="py-12 sm:py-16">
            <div class="dc-container">
                <section class="rounded-[2rem] border border-green-100 bg-white p-6 shadow-soft sm:p-10">
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-brand-dark">{{ __('DailyCart') }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-brand-text sm:text-5xl">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-4 max-w-3xl text-base leading-8 text-brand-text/70">{{ $subtitle }}</p>
                    @endif
                    @if ($ctaLabel && $ctaUrl)
                        <a class="dc-button mt-6" href="{{ url($ctaUrl) }}">{{ $ctaLabel }}</a>
                    @endif
                </section>

                <section class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <div class="space-y-4 text-sm leading-8 text-brand-text/75">
                            {!! nl2br(e($body)) !!}
                        </div>

                        @if ($page === 'contact')
                            <form method="POST" action="{{ route('pages.contact.store') }}" class="mt-8 grid gap-4">
                                @csrf
                                @if (session('contact_status'))
                                    <div class="rounded-lg bg-green-50 p-4 text-sm font-medium text-green-700">{{ session('contact_status') }}</div>
                                @endif
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <input name="name" value="{{ old('name') }}" class="rounded-2xl border-gray-200" placeholder="{{ __('Name') }}" required>
                                    <input name="email" type="email" value="{{ old('email') }}" class="rounded-2xl border-gray-200" placeholder="{{ __('Email') }}" required>
                                </div>
                                <input name="phone" value="{{ old('phone') }}" class="rounded-2xl border-gray-200" placeholder="{{ __('Phone') }}">
                                <input name="subject" value="{{ old('subject') }}" class="rounded-2xl border-gray-200" placeholder="{{ __('Subject') }}" required>
                                <textarea name="message" rows="5" class="rounded-2xl border-gray-200" placeholder="{{ __('Message') }}" required>{{ old('message') }}</textarea>
                                <button class="dc-button justify-self-start" type="submit">{{ __('Send Message') }}</button>
                            </form>
                        @endif
                    </article>

                    <aside class="space-y-6">
                        <div class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">{{ __('Details') }}</h2>
                            <div class="mt-4 space-y-3 text-sm leading-7 text-brand-text/70">
                                @if ($email)
                                    <p><span class="font-semibold text-brand-text">{{ __('Email') }}:</span> {{ $email }}</p>
                                @endif
                                @if ($phone)
                                    <p><span class="font-semibold text-brand-text">{{ __('Phone') }}:</span> {{ $phone }}</p>
                                @endif
                                @if ($address)
                                    <p><span class="font-semibold text-brand-text">{{ __('Address') }}:</span> {{ $address }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($page === 'offers')
                            <div class="rounded-3xl border border-orange-100 bg-orange-50 p-6 shadow-sm">
                                <h2 class="text-lg font-bold">{{ __('Offer Note') }}</h2>
                                <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Offer availability can change by stock, vendor approval, and promotion validity.') }}</p>
                            </div>
                        @endif
                    </aside>
                </section>

                @if ($page === 'offers')
                    <section class="mt-8">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="font-semibold text-brand-dark">{{ __('Live Promotions') }}</p>
                                <h2 class="mt-2 text-2xl font-extrabold">{{ __('Active offers') }}</h2>
                            </div>
                            <a class="dc-button-secondary" href="{{ route('products.index') }}">{{ __('Browse Products') }}</a>
                        </div>

                        <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @forelse (($promotions ?? collect()) as $promotion)
                                <article class="rounded-3xl bg-white p-6 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-wide text-brand-dark">{{ $promotion->promotion_type }}</p>
                                    <h3 class="mt-2 text-xl font-bold">{{ $promotion->title }}</h3>
                                    <p class="mt-3 line-clamp-3 text-sm leading-7 text-brand-text/70">{{ $promotion->description }}</p>
                                    <p class="mt-4 text-sm font-semibold text-brand-orange">{{ __('Valid until') }} {{ $promotion->ends_at?->format('M d, Y') }}</p>
                                </article>
                            @empty
                                <div class="dc-card md:col-span-2 xl:col-span-3">
                                    <p class="text-sm text-brand-text/70">{{ __('No active offers are available right now. Please check again soon.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endif
            </div>
        </main>

        <x-footer />
    </body>
</html>
