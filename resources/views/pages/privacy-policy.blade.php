<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Privacy Policy') }} | DailyCart</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-brand-light font-sans text-brand-text antialiased">
        <header class="sticky top-0 z-40 border-b border-green-100 bg-white/90 backdrop-blur-xl">
            <div class="dc-container flex h-20 items-center justify-between">
                <a href="/" class="transition hover:scale-[1.02]"><x-application-logo /></a>
                <nav class="hidden items-center gap-3 md:flex">
                    <a class="dc-button-secondary" href="{{ route('pages.refund-policy') }}">{{ __('Refund Policy') }}</a>
                    <a class="dc-button-secondary" href="{{ route('pages.terms-and-conditions') }}">{{ __('Terms') }}</a>
                    <a class="dc-button" href="{{ route('login') }}">{{ __('Login') }}</a>
                </nav>
            </div>
        </header>

        <main class="py-12 sm:py-16">
            <div class="dc-container">
                <section class="rounded-[2rem] border border-green-100 bg-white p-6 shadow-soft sm:p-10">
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-brand-dark">{{ __('DailyCart Legal') }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-brand-text sm:text-5xl">{{ __('Privacy Policy') }}</h1>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-brand-text/70">{{ __('This Privacy Policy explains how DailyCart collects, uses, protects, and shares information for our online grocery and daily essentials delivery platform in Sri Lanka. DailyCart operates in English only and supports LKR payments only.') }}</p>
                </section>

                <section class="mt-8 grid gap-6 lg:grid-cols-2">
                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Information We Collect') }}</h2>
                        <ul class="mt-3 space-y-2 text-sm leading-7 text-brand-text/70">
                            <li>{{ __('Customer name, phone number, email address, delivery address, and account profile details.') }}</li>
                            <li>{{ __('Order details, cart items, products purchased, refund requests, support tickets, reviews, and communication history.') }}</li>
                            <li>{{ __('Payment information such as payment method, payment status, transaction references, order totals, and payment gateway responses. DailyCart does not store card numbers.') }}</li>
                            <li>{{ __('Location data used for address selection, delivery distance, vendor location, rider tracking, and delivery proof where enabled.') }}</li>
                        </ul>
                    </article>

                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Cookies and Similar Technologies') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart may use cookies and session data to keep users logged in, protect forms, remember preferences, improve performance, measure usage, and support secure checkout. Users can manage cookies through their browser settings, but some platform features may not work correctly if cookies are disabled.') }}</p>
                    </article>

                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('How Information Is Used') }}</h2>
                        <ul class="mt-3 space-y-2 text-sm leading-7 text-brand-text/70">
                            <li>{{ __('To create and manage customer, vendor, rider, admin, and support accounts.') }}</li>
                            <li>{{ __('To process orders, schedule deliveries, assign riders, collect payments, manage refunds, and provide customer support.') }}</li>
                            <li>{{ __('To send welcome messages, OTP verification, order updates, payment updates, refund updates, approval notices, support replies, and promotional notifications where allowed.') }}</li>
                            <li>{{ __('To prevent fraud, protect accounts, maintain activity logs, improve service quality, and comply with applicable requirements.') }}</li>
                        </ul>
                    </article>

                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Third-party Services') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart may share necessary information with trusted service providers such as PayHere or another payment gateway, Google Maps, SMS providers, email/SMTP services, Firebase or push notification services, hosting providers, and analytics or security tools. These providers receive only the information needed to perform their service.') }}</p>
                    </article>

                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Data Security') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('We use reasonable administrative, technical, and organizational safeguards to protect personal data, payment records, account access, and delivery information. No online system is completely risk free, so customers should keep account credentials and OTP codes private.') }}</p>
                    </article>

                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('User Rights') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Users may request access, correction, update, or deletion of eligible personal information by contacting DailyCart. Some records may be retained where needed for order history, payment records, refund handling, security, legal compliance, or dispute resolution.') }}</p>
                    </article>

                    <article class="rounded-3xl border border-orange-100 bg-orange-50 p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Policy Updates') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart may update this Privacy Policy when platform features, integrations, payment methods, or legal requirements change. The latest version will be published on this page.') }}</p>
                    </article>

                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Contact Details') }}</h2>
                        <div class="mt-3 space-y-2 text-sm leading-7 text-brand-text/70">
                            <p>{{ __('Business Name') }}: DailyCart</p>
                            <p>{{ __('Email') }}: uahsens1@gmail.com</p>
                            <p>{{ __('Phone') }}: +94 75 460 3008</p>
                            <p>{{ __('Address') }}: Batticaloa, Sri Lanka</p>
                        </div>
                    </article>
                </section>
            </div>
        </main>

        <x-footer />
    </body>
</html>
