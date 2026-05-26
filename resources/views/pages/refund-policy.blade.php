<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Refund Policy') }} | DailyCart</title>
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
                    <a class="dc-button-secondary" href="{{ route('pages.privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                    <a class="dc-button-secondary" href="{{ route('pages.terms-and-conditions') }}">{{ __('Terms') }}</a>
                    <a class="dc-button" href="{{ route('login') }}">{{ __('Login') }}</a>
                </nav>
            </div>
        </header>

        <main class="py-12 sm:py-16">
            <div class="dc-container">
                <section class="rounded-[2rem] border border-green-100 bg-white p-6 shadow-soft sm:p-10">
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-brand-dark">{{ __('DailyCart Legal') }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-brand-text sm:text-5xl">{{ __('Refund Policy') }}</h1>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-brand-text/70">
                        {{ __('Thank you for shopping with DailyCart, an online grocery and daily essentials delivery platform operating in Sri Lanka. We aim to provide fresh, accurate, and reliable orders in LKR only.') }}
                    </p>
                    <div class="mt-6 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="rounded-3xl bg-brand-light p-4"><span class="font-bold text-brand-dark">{{ __('Business') }}</span><p>DailyCart</p></div>
                        <div class="rounded-3xl bg-brand-light p-4"><span class="font-bold text-brand-dark">{{ __('Country') }}</span><p>{{ __('Sri Lanka') }}</p></div>
                        <div class="rounded-3xl bg-brand-light p-4"><span class="font-bold text-brand-dark">{{ __('Currency') }}</span><p>{{ __('LKR only') }}</p></div>
                    </div>
                </section>

                <section class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <div class="space-y-6">
                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Return Eligibility') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Returns may be accepted when an item is damaged, spoiled, expired, missing, incorrectly delivered, or materially different from the item ordered. The product must be unused, in its original packaging where possible, and supported by order details and clear photos when requested.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Return Period and Reporting Time') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Eligible return requests must be made within 2 days from delivery. Damaged, spoiled, expired, or wrong item issues must be reported within 24 hours of delivery so our team can review the order quickly and fairly.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Perishable Goods Policy') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Fresh vegetables, fruits, bakery items, dairy products, frozen food, and other perishable goods are reviewed case by case. We may request photos, package details, delivery proof, or pickup inspection before approving a refund or replacement.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Non-returnable Items') }}</h2>
                            <ul class="mt-3 space-y-2 text-sm leading-7 text-brand-text/70">
                                <li>{{ __('Opened or used personal care, baby care, pharmacy, hygiene, or sealed goods where return is unsafe or unsuitable.') }}</li>
                                <li>{{ __('Items damaged after delivery due to customer handling, storage, or delay in reporting.') }}</li>
                                <li>{{ __('Products ordered by mistake after the order has been delivered correctly.') }}</li>
                            </ul>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Refund Approval Process') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('After receiving your request, DailyCart will review the order, product condition, photos, vendor notes, rider delivery proof, and payment record. If approved, we may offer a refund, replacement, wallet credit, or other reasonable resolution depending on the issue.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Refund Method and COD Handling') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Approved refunds are processed to the original payment method where possible. For Cash on Delivery orders, refunds may be handled through DailyCart wallet credit, bank transfer, replacement, or another method agreed with the customer. Refunds are processed in LKR only.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Processing Time') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Approved refunds usually take 5-10 business days to process after approval. Bank, payment gateway, or wallet processing times may vary depending on the payment provider.') }}</p>
                        </article>
                    </div>

                    <aside class="space-y-6">
                        <div class="rounded-3xl border border-orange-100 bg-orange-50 p-6 shadow-sm">
                            <h2 class="text-lg font-bold">{{ __('Delivery Scheduling Rule') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Customers can schedule delivery only at least 30 minutes after placing the order.') }}</p>
                        </div>
                        <div class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">{{ __('Contact Details') }}</h2>
                            <div class="mt-3 space-y-2 text-sm leading-7 text-brand-text/70">
                                <p>{{ __('Email') }}: uahsens1@gmail.com</p>
                                <p>{{ __('Phone') }}: +94 75 460 3008</p>
                                <p>{{ __('Address') }}: Batticaloa, Sri Lanka</p>
                            </div>
                        </div>
                    </aside>
                </section>
            </div>
        </main>

        <x-footer />
    </body>
</html>
