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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>{{ $title }} | DailyCart</title>

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/logo.png') }}">

    <link
        rel="shortcut icon"
        type="image/png"
        href="{{ asset('images/logo.png') }}">

    <link
        rel="preconnect"
        href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap"
        rel="stylesheet">

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])
</head>

<body class="bg-brand-light font-sans text-brand-text antialiased">

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-green-100 bg-white/90 shadow-sm backdrop-blur-xl">
        <div class="dc-container flex min-h-16 items-center justify-between gap-3 py-2 sm:min-h-20">

            {{-- Logo --}}
            <a
                href="{{ route('home') }}"
                class="min-w-0 shrink transition duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20">

                <x-application-logo />
            </a>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">

                {{-- Back button --}}
                <a
                    class="dc-button-secondary inline-flex min-h-10 items-center gap-1.5 px-3 text-xs sm:px-5 sm:text-sm"
                    href="{{ route('home') }}"
                    aria-label="{{ __('Back to Home') }}">

                    <svg
                        class="h-4 w-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19 12H5m6-6-6 6 6 6">
                        </path>
                    </svg>

                    <span class="hidden sm:inline">
                        {{ __('Back to Home') }}
                    </span>
                </a>

                {{-- Desktop navigation --}}
                <nav
                    class="hidden items-center gap-2 lg:flex"
                    aria-label="{{ __('Main navigation') }}">

                    <a
                        class="dc-button-secondary"
                        href="{{ route('pages.about') }}">

                        {{ __('About') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('pages.offers') }}">

                        {{ __('Offers') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('stores.index') }}">

                        {{ __('Stores') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('pages.contact') }}">

                        {{ __('Contact') }}
                    </a>

                    @auth
                    <a
                        class="dc-button"
                        href="{{ route('dashboard') }}">

                        {{ __('Dashboard') }}
                    </a>
                    @else
                    <a
                        class="dc-button"
                        href="{{ route('login') }}">

                        {{ __('Login') }}
                    </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    {{-- Main content --}}
    <main class="py-8 sm:py-10 lg:py-12">
        <div class="dc-container">

            {{-- Hero section --}}
            <section class="relative overflow-hidden rounded-3xl border border-green-100 bg-white p-6 shadow-soft sm:p-8 {{ $page === 'about' ? 'dc-about-hero-3d' : '' }} {{ $page === 'contact' ? 'dc-contact-hero-3d' : '' }}">

                {{-- Decorative background --}}
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-brand-primary/5 blur-3xl"
                    aria-hidden="true">
                </div>

                <div
                    class="pointer-events-none absolute -bottom-28 left-1/3 h-48 w-48 rounded-full bg-brand-orange/5 blur-3xl"
                    aria-hidden="true">
                </div>

                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-dark">
                        {{ __('DailyCart') }}
                    </p>

                    <h1 class="mt-3 max-w-4xl text-2xl font-extrabold leading-tight tracking-tight text-brand-text sm:text-3xl lg:text-4xl">
                        {{ $title }}
                    </h1>

                    @if ($subtitle)
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-brand-text/70 sm:text-base">
                        {{ $subtitle }}
                    </p>
                    @endif

                    @if ($ctaLabel && $ctaUrl)
                    <a
                        class="dc-button mt-5"
                        href="{{ url($ctaUrl) }}">

                        {{ $ctaLabel }}
                    </a>
                    @endif
                </div>
            </section>

            @if ($page === 'contact')
            <style>
                .dc-contact-hero-3d {
                    animation: dc-contact-arrive .85s cubic-bezier(.2, .75, .2, 1) both;
                    transform-origin: 50% 100%;
                }

                .dc-contact-scene {
                    position: relative;
                    isolation: isolate;
                    perspective: 1400px;
                    perspective-origin: 50% 12%;
                }

                .dc-contact-scene::before,
                .dc-contact-scene::after {
                    content: '';
                    position: absolute;
                    z-index: -1;
                    border-radius: 9999px;
                    pointer-events: none;
                    filter: blur(3px);
                    animation: dc-contact-float 12s ease-in-out infinite;
                }

                .dc-contact-scene::before {
                    top: 4rem;
                    right: 1rem;
                    width: 13rem;
                    height: 13rem;
                    background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, .9), rgba(34, 197, 94, .2) 44%, transparent 74%);
                }

                .dc-contact-scene::after {
                    bottom: 8rem;
                    left: 0;
                    width: 16rem;
                    height: 16rem;
                    background: radial-gradient(circle, rgba(251, 146, 60, .18), rgba(21, 128, 61, .1) 52%, transparent 75%);
                    animation-delay: -5s;
                    animation-duration: 15s;
                }

                .dc-contact-card {
                    position: relative;
                    overflow: hidden;
                    transform-style: preserve-3d;
                    will-change: transform;
                    transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
                    box-shadow: 0 20px 48px -34px rgba(9, 68, 40, .65);
                }

                .dc-contact-card::after {
                    content: '';
                    position: absolute;
                    inset: 0;
                    z-index: 0;
                    pointer-events: none;
                    opacity: 0;
                    background: radial-gradient(circle at var(--glow-x, 50%) var(--glow-y, 50%), rgba(255, 255, 255, .5), transparent 38%);
                    transition: opacity .25s ease;
                }

                .dc-contact-card:hover {
                    border-color: rgba(22, 163, 74, .35);
                    box-shadow: 0 30px 60px -34px rgba(9, 68, 40, .55);
                }

                .dc-contact-card:hover::after {
                    opacity: 1;
                }

                .dc-contact-card>* {
                    position: relative;
                    z-index: 1;
                    transform: translateZ(14px);
                }

                .dc-contact-motion-ready [data-contact-reveal] {
                    opacity: 0;
                    transform: translateY(30px) rotateX(4deg);
                    transform-origin: 50% 100%;
                    transition: opacity .65s ease var(--contact-delay, 0ms), transform .8s cubic-bezier(.2, .75, .2, 1) var(--contact-delay, 0ms);
                }

                .dc-contact-motion-ready [data-contact-reveal].is-visible {
                    opacity: 1;
                    transform: translateY(0) rotateX(0);
                }

                @keyframes dc-contact-arrive {
                    from {
                        opacity: 0;
                        transform: translateY(24px) rotateX(4deg);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0) rotateX(0);
                    }
                }

                @keyframes dc-contact-float {

                    0%,
                    100% {
                        transform: translate3d(0, 0, 0) scale(1);
                    }

                    50% {
                        transform: translate3d(1.25rem, -1.75rem, 0) scale(1.08);
                    }
                }

                @media (max-width: 767px) {
                    .dc-contact-card {
                        transform: none !important;
                        will-change: auto;
                    }
                }

                @media (prefers-reduced-motion: reduce) {

                    .dc-contact-hero-3d,
                    .dc-contact-scene::before,
                    .dc-contact-scene::after,
                    .dc-contact-card,
                    .dc-contact-motion-ready [data-contact-reveal] {
                        animation: none !important;
                        opacity: 1 !important;
                        transform: none !important;
                        transition: none !important;
                    }
                }
            </style>
            @endif

            {{-- Page content and details --}}
            <section class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px] {{ $page === 'about' ? 'dc-about-intro-3d' : '' }} {{ $page === 'contact' ? 'dc-contact-scene' : '' }}" @if ($page==='contact' ) data-contact-scene data-contact-reveal @endif>

                {{-- Main content --}}
                <article class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8 {{ $page === 'contact' ? 'dc-contact-card' : '' }}" @if ($page==='contact' ) data-contact-tilt @endif>
                    <div class="space-y-4 text-sm leading-7 text-brand-text/75 sm:text-base">
                        {!! nl2br(e($body)) !!}
                    </div>

                    {{-- Contact form --}}
                    @if ($page === 'contact')
                    <form
                        method="POST"
                        action="{{ route('pages.contact.store') }}"
                        enctype="multipart/form-data"
                        class="mt-8 grid gap-4">

                        @csrf

                        @if (session('contact_status'))
                        <div
                            class="rounded-2xl border border-green-100 bg-green-50 p-4 text-sm font-medium text-green-700"
                            role="status">

                            {{ session('contact_status') }}
                        </div>
                        @endif

                        <div>
                            <p class="dc-page-eyebrow">{{ __('Send us a message') }}</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('How can we help?') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __('Share the details below and our support team will review your enquiry.') }}</p>
                        </div>

                        {{-- Full name and email --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="contact-name"
                                    class="mb-2 block text-sm font-semibold text-brand-text">

                                    {{ __('Full name') }}
                                </label>

                                <input
                                    id="contact-name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="{{ __('Your full name') }}"
                                    required>

                                @error('name')
                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="contact-email"
                                    class="mb-2 block text-sm font-semibold text-brand-text">

                                    {{ __('Email') }}
                                </label>

                                <input
                                    id="contact-email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="{{ __('Email address') }}"
                                    required>

                                @error('email')
                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Phone and enquiry type --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="contact-phone" class="mb-2 block text-sm font-semibold text-brand-text">{{ __('Phone number') }}</label>
                                <input
                                    id="contact-phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="{{ __('Your phone number') }}">
                                @error('phone')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact-enquiry-type" class="mb-2 block text-sm font-semibold text-brand-text">{{ __('Enquiry type') }}</label>
                                <select
                                    id="contact-enquiry-type"
                                    name="enquiry_type"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    required>
                                    <option value="">{{ __('Select an enquiry type') }}</option>
                                    @foreach ([
                                    'order_assistance' => 'Order assistance',
                                    'payment_problem' => 'Payment problem',
                                    'refund_or_cancellation' => 'Refund or cancellation',
                                    'delivery_problem' => 'Delivery problem',
                                    'product_complaint' => 'Product complaint',
                                    'vendor_support' => 'Vendor support',
                                    'rider_support' => 'Rider support',
                                    'account_problem' => 'Account problem',
                                    'technical_issue' => 'Technical issue',
                                    'general_enquiry' => 'General enquiry',
                                    ] as $enquiryValue => $enquiryLabel)
                                    <option value="{{ $enquiryValue }}" @selected(old('enquiry_type')===$enquiryValue)>{{ __($enquiryLabel) }}</option>
                                    @endforeach
                                </select>
                                @error('enquiry_type')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Subject and optional order number --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="contact-subject" class="mb-2 block text-sm font-semibold text-brand-text">{{ __('Subject') }}</label>
                                <input
                                    id="contact-subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="{{ __('Message subject') }}"
                                    required>
                                @error('subject')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact-order-number" class="mb-2 block text-sm font-semibold text-brand-text">
                                    {{ __('Order number') }} <span class="font-normal text-brand-text/50">{{ __('(optional)') }}</span>
                                </label>
                                <input
                                    id="contact-order-number"
                                    name="order_number"
                                    value="{{ old('order_number') }}"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="{{ __('For example: DC-1024') }}">
                                @error('order_number')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Message --}}
                        <div>
                            <label
                                for="contact-message"
                                class="mb-2 block text-sm font-semibold text-brand-text">

                                {{ __('Message') }}
                            </label>

                            <textarea
                                id="contact-message"
                                name="message"
                                rows="5"
                                class="w-full resize-y rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="{{ __('How can we help you?') }}"
                                required>{{ old('message') }}</textarea>

                            @error('message')
                            <p class="mt-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Optional attachment --}}
                        <div>
                            <label for="contact-attachment" class="mb-2 block text-sm font-semibold text-brand-text">
                                {{ __('Attachment') }} <span class="font-normal text-brand-text/50">{{ __('(optional)') }}</span>
                            </label>
                            <input
                                id="contact-attachment"
                                name="attachment"
                                type="file"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                class="block w-full rounded-2xl border border-brand-border bg-white px-4 py-3 text-sm text-brand-text file:mr-4 file:rounded-full file:border-0 file:bg-green-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-dark hover:file:bg-green-100">
                            <p class="mt-2 text-xs leading-5 text-brand-text/50">{{ __('JPG, PNG, PDF, DOC, or DOCX up to 5 MB.') }}</p>
                            @error('attachment')
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            class="dc-button w-full justify-center sm:w-fit sm:justify-self-start"
                            type="submit">

                            {{ __('Send Message') }}
                        </button>
                    </form>
                    @endif
                </article>

                {{-- Sidebar --}}
                <aside class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">

                    {{-- Contact details --}}
                    <div class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm {{ $page === 'contact' ? 'dc-contact-card' : '' }}" @if ($page==='contact' ) data-contact-tilt data-tilt-strength="5" @endif>
                        <h2 class="text-base font-extrabold text-brand-dark">
                            {{ $page === 'contact' ? __('Contact details') : __('Details') }}
                        </h2>

                        <div class="mt-4 space-y-4 text-sm leading-6 text-brand-text/70">
                            @if ($page === 'contact')
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">{{ __('Support email') }}</p>
                                <a href="mailto:uahsens1@gmail.com" class="mt-1 block break-all font-semibold text-brand-dark transition hover:text-brand-primary">uahsens1@gmail.com</a>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">{{ __('Customer service') }}</p>
                                <a href="tel:+94754603008" class="mt-1 block font-semibold text-brand-dark transition hover:text-brand-primary">+94 75 460 3008</a>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">{{ __('WhatsApp') }}</p>
                                <a href="https://wa.me/94754603008" target="_blank" rel="noopener noreferrer" class="mt-1 block font-semibold text-brand-dark transition hover:text-brand-primary">+94 75 460 3008</a>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">{{ __('Office address') }}</p>
                                <p class="mt-1 font-semibold text-brand-dark">{{ __('#479 Ofnaa Ex-Chairman Road, Oddamavadi-02') }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 border-t border-brand-border pt-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">{{ __('Business hours') }}</p>
                                    <p class="mt-1 font-semibold text-brand-dark">{{ __('Mon–Sun, 24/7') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">{{ __('Response time') }}</p>
                                    <p class="mt-1 font-semibold text-brand-dark">{{ __('8:00 AM–12:00 AM') }}</p>
                                </div>
                            </div>
                            @else
                            @if ($email)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">
                                    {{ __('Email') }}
                                </p>

                                <a
                                    href="mailto:{{ $email }}"
                                    class="mt-1 block break-all font-medium transition hover:text-brand-primary">

                                    {{ $email }}
                                </a>
                            </div>
                            @endif

                            @if ($phone)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">
                                    {{ __('Phone') }}
                                </p>

                                <a
                                    href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
                                    class="mt-1 block font-medium transition hover:text-brand-primary">

                                    {{ $phone }}
                                </a>
                            </div>
                            @endif

                            @if ($address)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">
                                    {{ __('Address') }}
                                </p>

                                <p class="mt-1 font-medium">
                                    {{ $address }}
                                </p>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>

                    @if ($page === 'contact')
                    <div class="dc-contact-card rounded-3xl border border-orange-200 bg-gradient-to-br from-orange-50 to-amber-50 p-5 shadow-sm" data-contact-tilt data-tilt-strength="4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-700">{{ __('Important') }}</p>
                        <h2 class="mt-2 text-base font-extrabold text-brand-text">{{ __('Not for medical emergencies') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-brand-text/70">{{ __('Do not use this contact form for a medical emergency. Contact your local emergency service or nearest medical facility immediately.') }}</p>
                    </div>
                    @endif

                    {{-- Offer note --}}
                    @if ($page === 'offers')
                    <div class="rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-amber-50 p-4 shadow-sm">
                        <h2 class="text-sm font-extrabold text-brand-dark">
                            {{ __('Offer Note') }}
                        </h2>

                        <p class="mt-2 text-xs leading-6 text-brand-text/70 sm:text-sm">
                            {{ __('Offer availability can change by stock, vendor approval, and promotion validity.') }}
                        </p>
                    </div>
                    @endif
                </aside>
            </section>

            @if ($page === 'contact')
            <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-contact-reveal aria-label="{{ __('Contact shortcuts') }}">
                <a href="#contact-faq" class="dc-contact-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm" data-contact-tilt data-tilt-strength="5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-50 text-xl text-brand-dark" aria-hidden="true">?</span>
                    <h2 class="mt-4 text-lg font-extrabold text-brand-text">{{ __('Frequently asked questions') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __('Find quick answers before sending a message.') }}</p>
                    <span class="mt-4 inline-flex text-sm font-bold text-brand-primary">{{ __('View FAQs') }} →</span>
                </a>

                @auth
                <a href="{{ route('support.tickets.create') }}" class="dc-contact-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm" data-contact-tilt data-tilt-strength="5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-50 text-xl text-brand-dark" aria-hidden="true">✉</span>
                    <h2 class="mt-4 text-lg font-extrabold text-brand-text">{{ __('Open a support ticket') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __('Keep your support request connected to your DailyCart account.') }}</p>
                    <span class="mt-4 inline-flex text-sm font-bold text-brand-primary">{{ __('Create ticket') }} →</span>
                </a>
                @endauth

                <a href="{{ auth()->check() && auth()->user()->hasPrimaryRole('Customer') ? route('customer.orders.index') : route('login') }}" class="dc-contact-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm" data-contact-tilt data-tilt-strength="5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-50 text-xl text-brand-dark" aria-hidden="true">⌖</span>
                    <h2 class="mt-4 text-lg font-extrabold text-brand-text">{{ __('Track an order') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __('Open your orders to review the latest delivery progress.') }}</p>
                    <span class="mt-4 inline-flex text-sm font-bold text-brand-primary">{{ __('Order tracking') }} →</span>
                </a>
            </section>

            <section class="mt-8 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]" data-contact-reveal aria-label="{{ __('Directions and frequently asked questions') }}">
                <article class="dc-contact-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-contact-tilt data-tilt-strength="5">
                    <p class="dc-page-eyebrow">{{ __('Visit DailyCart') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Map and directions') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-brand-text/65">{{ __('#479 Ofnaa Ex-Chairman Road, Oddamavadi-02') }}</p>
                    <a
                        class="dc-button mt-5"
                        href="https://www.google.com/maps/dir/?api=1&destination=479%20Ofnaa%20Ex-Chairman%20Road%2C%20Oddamavadi-02%2C%20Sri%20Lanka"
                        target="_blank"
                        rel="noopener noreferrer">
                        {{ __('Get directions') }}
                    </a>
                </article>

                <div class="dc-contact-card overflow-hidden rounded-3xl border border-brand-border bg-white p-2 shadow-sm" data-contact-tilt data-tilt-strength="3">
                    <iframe
                        class="h-72 w-full rounded-[1.25rem] border-0 lg:h-full lg:min-h-80"
                        src="https://www.google.com/maps?q=479%20Ofnaa%20Ex-Chairman%20Road%2C%20Oddamavadi-02%2C%20Sri%20Lanka&output=embed"
                        title="{{ __('DailyCart office location map') }}"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </section>

            <section id="contact-faq" class="dc-contact-card mt-8 scroll-mt-28 rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-contact-reveal data-contact-tilt data-tilt-strength="3">
                <div class="max-w-2xl">
                    <p class="dc-page-eyebrow">{{ __('Quick help') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Frequently asked questions') }}</h2>
                </div>

                <div class="mt-6 grid gap-3">
                    @foreach ([
                    ['How can I track an order?', 'Log in to your customer account, open Orders, and select the order you want to follow.'],
                    ['What should I include for a payment or delivery problem?', 'Include your order number, a clear description, and an optional screenshot or document.'],
                    ['When should I expect a reply?', 'The support response window is 8:00 AM to 12:00 AM. Response time may vary with enquiry volume.'],
                    ['Can vendors and riders contact this team?', 'Yes. Select Vendor support or Rider support in the enquiry type so the request reaches the appropriate team.'],
                    ] as [$question, $answer])
                    <details class="group rounded-2xl border border-green-100 bg-brand-light/50 p-4">
                        <summary class="cursor-pointer list-none pr-8 font-bold text-brand-text marker:hidden">{{ __($question) }}</summary>
                        <p class="mt-3 text-sm leading-6 text-brand-text/65">{{ __($answer) }}</p>
                    </details>
                    @endforeach
                </div>
            </section>

            <script>
                (() => {
                    const scene = document.querySelector('[data-contact-scene]');

                    if (!scene || scene.dataset.animated === 'true') {
                        return;
                    }

                    scene.dataset.animated = 'true';

                    const pageRegion = scene.closest('.dc-container') || document;
                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const reveals = Array.from(pageRegion.querySelectorAll('[data-contact-reveal]'));

                    if (reducedMotion) {
                        reveals.forEach((element) => element.classList.add('is-visible'));
                        return;
                    }

                    pageRegion.classList.add('dc-contact-motion-ready');

                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach((entry) => {
                                if (!entry.isIntersecting) {
                                    return;
                                }

                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            });
                        }, {
                            threshold: 0.1,
                            rootMargin: '0px 0px -7% 0px',
                        });

                        reveals.forEach((element, index) => {
                            element.style.setProperty('--contact-delay', `${Math.min(index * 70, 280)}ms`);
                            observer.observe(element);
                        });
                    } else {
                        reveals.forEach((element) => element.classList.add('is-visible'));
                    }

                    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                        return;
                    }

                    pageRegion.querySelectorAll('[data-contact-tilt]').forEach((card) => {
                        const strength = Number(card.dataset.tiltStrength || 7);

                        card.addEventListener('pointermove', (event) => {
                            const rect = card.getBoundingClientRect();
                            const x = (event.clientX - rect.left) / rect.width - 0.5;
                            const y = (event.clientY - rect.top) / rect.height - 0.5;

                            card.style.setProperty('--glow-x', `${(x + 0.5) * 100}%`);
                            card.style.setProperty('--glow-y', `${(y + 0.5) * 100}%`);
                            card.style.transform = `perspective(900px) rotateX(${(-y * strength).toFixed(2)}deg) rotateY(${(x * strength).toFixed(2)}deg) translateY(-5px)`;
                        });

                        card.addEventListener('pointerleave', () => {
                            card.style.removeProperty('transform');
                            card.style.removeProperty('--glow-x');
                            card.style.removeProperty('--glow-y');
                        });
                    });
                })();
            </script>
            @endif

            @if ($page === 'about')
            <style>
                .dc-about-hero-3d,
                .dc-about-intro-3d {
                    animation: dc-about-arrive .85s cubic-bezier(.2, .75, .2, 1) both;
                    transform-origin: 50% 100%;
                }

                .dc-about-intro-3d {
                    animation-delay: .1s;
                }

                .dc-about-scene {
                    isolation: isolate;
                    perspective: 1400px;
                    perspective-origin: 50% 18%;
                }

                .dc-about-atmosphere {
                    position: absolute;
                    inset: -5rem 0;
                    z-index: -1;
                    overflow: hidden;
                    pointer-events: none;
                }

                .dc-about-orb {
                    position: absolute;
                    display: block;
                    border-radius: 9999px;
                    opacity: .3;
                    filter: blur(2px);
                    animation: dc-about-float 10s ease-in-out infinite;
                }

                .dc-about-orb-one {
                    top: 2%;
                    right: 2%;
                    width: 12rem;
                    height: 12rem;
                    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .95), rgba(34, 197, 94, .22) 42%, transparent 72%);
                }

                .dc-about-orb-two {
                    top: 42%;
                    left: -4rem;
                    width: 15rem;
                    height: 15rem;
                    background: radial-gradient(circle at 50% 50%, rgba(251, 146, 60, .24), rgba(21, 128, 61, .12) 52%, transparent 74%);
                    animation-delay: -3.5s;
                    animation-duration: 13s;
                }

                .dc-about-orb-three {
                    right: 4%;
                    bottom: 4%;
                    width: 18rem;
                    height: 18rem;
                    background: radial-gradient(circle at 45% 45%, rgba(74, 222, 128, .2), rgba(255, 255, 255, .55) 55%, transparent 76%);
                    animation-delay: -7s;
                    animation-duration: 15s;
                }

                .dc-about-card {
                    position: relative;
                    overflow: hidden;
                    transform-style: preserve-3d;
                    will-change: transform;
                    transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
                    box-shadow: 0 18px 45px -34px rgba(9, 68, 40, .65);
                }

                .dc-about-card::after {
                    content: '';
                    position: absolute;
                    inset: 0;
                    z-index: 0;
                    pointer-events: none;
                    opacity: 0;
                    background: radial-gradient(circle at var(--glow-x, 50%) var(--glow-y, 50%), rgba(255, 255, 255, .42), transparent 38%);
                    transition: opacity .25s ease;
                }

                .dc-about-card:hover {
                    border-color: rgba(22, 163, 74, .35);
                    box-shadow: 0 28px 58px -32px rgba(9, 68, 40, .55);
                }

                .dc-about-card:hover::after {
                    opacity: 1;
                }

                .dc-about-card>* {
                    position: relative;
                    z-index: 1;
                    transform: translateZ(16px);
                }

                .dc-about-card>.pointer-events-none {
                    z-index: 0;
                    transform: translateZ(2px);
                }

                .dc-about-motion-ready [data-about-reveal] {
                    opacity: 0;
                    transform: translateY(32px) rotateX(4deg);
                    transform-origin: 50% 100%;
                    transition: opacity .65s ease var(--reveal-delay, 0ms), transform .8s cubic-bezier(.2, .75, .2, 1) var(--reveal-delay, 0ms);
                }

                .dc-about-motion-ready [data-about-reveal].is-visible {
                    opacity: 1;
                    transform: translateY(0) rotateX(0);
                }

                @keyframes dc-about-arrive {
                    from {
                        opacity: 0;
                        transform: translateY(24px) rotateX(4deg);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0) rotateX(0);
                    }
                }

                @keyframes dc-about-float {

                    0%,
                    100% {
                        transform: translate3d(0, 0, 0) scale(1);
                    }

                    50% {
                        transform: translate3d(1.5rem, -2rem, 0) scale(1.08);
                    }
                }

                @media (max-width: 767px) {
                    .dc-about-card {
                        transform: none !important;
                        will-change: auto;
                    }
                }

                @media (prefers-reduced-motion: reduce) {

                    .dc-about-hero-3d,
                    .dc-about-intro-3d,
                    .dc-about-orb,
                    .dc-about-card,
                    .dc-about-motion-ready [data-about-reveal] {
                        animation: none !important;
                        opacity: 1 !important;
                        transform: none !important;
                        transition: none !important;
                    }
                }
            </style>

            <section class="dc-about-scene relative mt-8 space-y-8" data-about-scene aria-label="{{ __('About DailyCart') }}">
                <div class="dc-about-atmosphere" aria-hidden="true">
                    <span class="dc-about-orb dc-about-orb-one"></span>
                    <span class="dc-about-orb dc-about-orb-two"></span>
                    <span class="dc-about-orb dc-about-orb-three"></span>
                </div>

                <div class="grid gap-6 lg:grid-cols-2" data-about-reveal>
                    <article class="dc-about-card relative overflow-hidden rounded-3xl border border-green-100 bg-gradient-to-br from-brand-dark to-brand-primary p-6 text-white shadow-soft sm:p-8" data-about-tilt>
                        <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10" aria-hidden="true"></div>
                        <div class="relative">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-100">{{ __('Our mission') }}</p>
                            <h2 class="mt-3 text-2xl font-extrabold leading-tight text-white sm:text-3xl">{{ __('Daily essentials within easier reach.') }}</h2>
                            <p class="mt-4 max-w-xl text-sm leading-7 text-white/85 sm:text-base">
                                {{ __('Our mission is to make daily essentials accessible through fast, reliable local delivery while helping trusted local businesses serve more households.') }}
                            </p>
                        </div>
                    </article>

                    <article class="dc-about-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-about-tilt>
                        <p class="dc-page-eyebrow">{{ __('Our story') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Built for everyday shopping in Sri Lanka') }}</h2>
                        <p class="mt-4 text-sm leading-7 text-brand-text/70 sm:text-base">
                            {{ __('DailyCart began with a simple idea: bring customers, local vendors, and delivery riders together in one dependable marketplace. From our roots in Batticaloa, we are building a practical shopping experience centred on local availability, clear order updates, and reliable support.') }}
                        </p>
                    </article>
                </div>

                <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-about-reveal>
                    <div class="max-w-2xl">
                        <p class="dc-page-eyebrow">{{ __('How DailyCart works') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('From local store to your door') }}</h2>
                    </div>

                    <ol class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                        ['Browse', 'Explore products and approved local stores.'],
                        ['Build your cart', 'Choose the items and quantities you need.'],
                        ['Checkout', 'Select your delivery and payment options.'],
                        ['Track delivery', 'Follow your order until it reaches you.'],
                        ] as $index => [$step, $description])
                        <li class="dc-about-card rounded-2xl border border-green-100 bg-brand-light/60 p-5" data-about-tilt data-tilt-strength="5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-primary text-sm font-extrabold text-white">{{ $index + 1 }}</span>
                            <h3 class="mt-4 font-extrabold text-brand-text">{{ __($step) }}</h3>
                            <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __($description) }}</p>
                        </li>
                        @endforeach
                    </ol>
                </section>

                <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]" data-about-reveal>
                    <section class="dc-about-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-about-tilt>
                        <p class="dc-page-eyebrow">{{ __('What you can shop') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Essentials for every day') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Browse useful products from approved vendors across the DailyCart marketplace.') }}</p>
                        <div class="mt-6 flex flex-wrap gap-2.5">
                            @foreach (['Groceries', 'Vegetables', 'Fruits', 'Bakery', 'Household goods', 'Pharmacy products', 'Beverages', 'Personal care'] as $category)
                            <span class="rounded-full border border-green-100 bg-green-50 px-4 py-2 text-sm font-semibold text-brand-dark">{{ __($category) }}</span>
                            @endforeach
                        </div>
                        <a class="dc-button-secondary mt-6" href="{{ route('categories.index') }}">{{ __('Browse categories') }}</a>
                    </section>

                    <section class="dc-about-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-about-tilt>
                        <p class="dc-page-eyebrow">{{ __('Why choose DailyCart') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Shopping built around trust') }}</h2>
                        <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach (['Approved local vendors', 'Secure checkout', 'Reliable delivery', 'Order tracking', 'Multiple payment methods', 'Customer support'] as $benefit)
                            <li class="flex items-start gap-3 rounded-2xl bg-brand-light/60 p-4 text-sm font-semibold text-brand-text">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-primary text-xs text-white" aria-hidden="true">✓</span>
                                <span>{{ __($benefit) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </section>
                </div>

                <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-about-reveal>
                    <div class="max-w-2xl">
                        <p class="dc-page-eyebrow">{{ __('Our marketplace') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Powered by people working together') }}</h2>
                    </div>
                    <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                        ['Customers', 'Discover stores, shop essentials, and track every order.'],
                        ['Vendors', 'Manage storefronts, products, promotions, and fulfilment.'],
                        ['Riders', 'Coordinate deliveries and keep customers informed.'],
                        ['DailyCart team', 'Support the marketplace, safety, and service quality.'],
                        ] as [$participant, $description])
                        <article class="dc-about-card rounded-2xl border border-brand-border bg-white p-5" data-about-tilt data-tilt-strength="5">
                            <h3 class="font-extrabold text-brand-dark">{{ __($participant) }}</h3>
                            <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __($description) }}</p>
                        </article>
                        @endforeach
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-2" data-about-reveal>
                    <section class="dc-about-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-about-tilt>
                        <p class="dc-page-eyebrow">{{ __('Service area') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Growing from Batticaloa') }}</h2>
                        <p class="mt-4 text-sm leading-7 text-brand-text/70">
                            {{ __('DailyCart serves Batticaloa and other locations enabled through our active delivery network. Product and delivery availability may vary by vendor, address, and configured delivery zone. Confirm availability for your location during checkout.') }}
                        </p>
                    </section>

                    <section class="dc-about-card rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8" data-about-tilt>
                        <p class="dc-page-eyebrow">{{ __('Our values') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('What guides DailyCart') }}</h2>
                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm font-semibold text-brand-text sm:grid-cols-3">
                            @foreach (['Reliability', 'Customer care', 'Fair marketplace', 'Product quality', 'Data privacy', 'Local business support'] as $value)
                            <div class="rounded-2xl bg-brand-light/70 px-4 py-3 text-center">{{ __($value) }}</div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <section class="overflow-hidden rounded-3xl bg-brand-text p-6 text-white shadow-soft sm:p-8" data-about-reveal>
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-200">{{ __('Join DailyCart') }}</p>
                            <h2 class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ __('Shop, sell, or deliver with us') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-white/70">{{ __('Choose the DailyCart experience that fits you and become part of a growing local marketplace.') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a class="dc-button" href="{{ route('products.index') }}">{{ __('Start Shopping') }}</a>
                            <a class="dc-button-secondary" href="{{ route('stores.index') }}">{{ __('Browse Stores') }}</a>
                            <a class="dc-button-secondary" href="{{ route('vendor.register') }}">{{ __('Become a Vendor') }}</a>
                            <a class="dc-button-secondary" href="{{ route('rider.register') }}">{{ __('Become a Rider') }}</a>
                        </div>
                    </div>
                </section>

                <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 rounded-3xl border border-brand-border bg-white px-6 py-5 text-sm font-semibold text-brand-dark shadow-sm" data-about-reveal aria-label="{{ __('DailyCart policies') }}">
                    <span class="text-brand-text/55">{{ __('Learn about your rights and responsibilities:') }}</span>
                    <a class="underline decoration-green-200 underline-offset-4 hover:text-brand-primary" href="{{ route('pages.privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                    <a class="underline decoration-green-200 underline-offset-4 hover:text-brand-primary" href="{{ route('pages.refund-policy') }}">{{ __('Refund Policy') }}</a>
                    <a class="underline decoration-green-200 underline-offset-4 hover:text-brand-primary" href="{{ route('pages.terms-and-conditions') }}">{{ __('Terms and Conditions') }}</a>
                </nav>
            </section>

            <script>
                (() => {
                    const scene = document.querySelector('[data-about-scene]');

                    if (!scene || scene.dataset.animated === 'true') {
                        return;
                    }

                    scene.dataset.animated = 'true';

                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const reveals = Array.from(scene.querySelectorAll('[data-about-reveal]'));

                    if (reducedMotion) {
                        reveals.forEach((element) => element.classList.add('is-visible'));
                        return;
                    }

                    scene.classList.add('dc-about-motion-ready');

                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach((entry) => {
                                if (!entry.isIntersecting) {
                                    return;
                                }

                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            });
                        }, {
                            threshold: 0.12,
                            rootMargin: '0px 0px -8% 0px',
                        });

                        reveals.forEach((element, index) => {
                            element.style.setProperty('--reveal-delay', `${Math.min(index * 65, 260)}ms`);
                            observer.observe(element);
                        });
                    } else {
                        reveals.forEach((element) => element.classList.add('is-visible'));
                    }

                    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                        return;
                    }

                    scene.querySelectorAll('[data-about-tilt]').forEach((card) => {
                        const strength = Number(card.dataset.tiltStrength || 7);

                        card.addEventListener('pointermove', (event) => {
                            const rect = card.getBoundingClientRect();
                            const x = (event.clientX - rect.left) / rect.width - 0.5;
                            const y = (event.clientY - rect.top) / rect.height - 0.5;

                            card.style.setProperty('--glow-x', `${(x + 0.5) * 100}%`);
                            card.style.setProperty('--glow-y', `${(y + 0.5) * 100}%`);
                            card.style.transform = `perspective(900px) rotateX(${(-y * strength).toFixed(2)}deg) rotateY(${(x * strength).toFixed(2)}deg) translateY(-5px)`;
                        });

                        card.addEventListener('pointerleave', () => {
                            card.style.removeProperty('transform');
                            card.style.removeProperty('--glow-x');
                            card.style.removeProperty('--glow-y');
                        });
                    });
                })();
            </script>
            @endif

            {{-- Offers --}}
            @if ($page === 'offers')
            <div class="mx-auto mt-5 max-w-6xl">
                <x-offers-today
                    :promotions="$promotions ?? collect()"
                    :contained="true" />
            </div>
            @endif
        </div>
    </main>

    <x-footer />
</body>

</html>