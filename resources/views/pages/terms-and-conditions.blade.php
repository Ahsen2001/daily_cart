<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Terms and Conditions') }} | DailyCart</title>
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
                    <a class="dc-button-secondary" href="{{ route('pages.privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                    <a class="dc-button" href="{{ route('login') }}">{{ __('Login') }}</a>
                </nav>
            </div>
        </header>

        <main class="py-12 sm:py-16">
            <div class="dc-container">
                <section class="rounded-[2rem] border border-green-100 bg-white p-6 shadow-soft sm:p-10">
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-brand-dark">{{ __('DailyCart Legal') }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-brand-text sm:text-5xl">{{ __('Terms and Conditions') }}</h1>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-brand-text/70">{{ __('These Terms and Conditions govern use of DailyCart, an online grocery and daily essentials delivery platform in Sri Lanka. By using DailyCart, you agree to these terms, our Privacy Policy, and our Refund Policy.') }}</p>
                </section>

                <section class="mt-8 space-y-6">
                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Website Usage and Minimum Age') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart is provided for lawful personal or business purchasing of groceries and daily essentials. Users must be at least 18 years old or use the platform under the supervision of a parent or legal guardian.') }}</p>
                    </article>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Account Responsibility') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Users are responsible for providing accurate account information, keeping login credentials and OTP codes secure, and immediately notifying DailyCart of unauthorized account activity. DailyCart may suspend accounts involved in fraud, abuse, or misuse.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Product Information and Availability') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Product descriptions, images, stock levels, expiry dates, prices, and availability may be updated by DailyCart, vendors, or admins. We try to keep information accurate, but availability may change before order confirmation.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Pricing and Currency') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('All prices, charges, refunds, wallet balances, coupons, delivery fees, and service charges are displayed and processed in LKR only. DailyCart may update pricing, delivery charges, and service fees from time to time.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Order Placement') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('An order is placed when the customer submits checkout details and DailyCart creates an order record. Orders may be split by vendor. DailyCart or a vendor may reject, cancel, or adjust an order if items are unavailable, incorrect, unsafe to deliver, or affected by system or pricing errors.') }}</p>
                        </article>

                        <article class="rounded-3xl border border-orange-100 bg-orange-50 p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Delivery Scheduling Rule') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Customers can schedule same-day or future delivery, but the selected delivery time must be at least 30 minutes after placing the order. Delivery times are estimates and may be affected by product preparation, rider availability, traffic, weather, or operational issues.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Payment Methods') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart may support Cash on Delivery, card payment through PayHere or another payment gateway, bank transfer, and wallet payment. Card details are handled by the payment gateway. Payment status must be completed where required before order processing or delivery completion.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Cancellation Policy') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Customers may cancel an order only while it is pending, unless DailyCart approves another cancellation due to exceptional circumstances. Vendors and admins may cancel orders for unavailable products, invalid order details, payment issues, delivery restrictions, or suspected misuse.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Refund Policy Reference') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Refunds, replacements, return eligibility, COD refund handling, and refund processing times are governed by the DailyCart Refund Policy available on this website.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Customer Responsibilities') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Customers must provide accurate delivery details, be available at the scheduled time, check products after delivery, report issues within the required period, pay all applicable amounts, and use DailyCart respectfully and lawfully.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Vendor Responsibilities') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Vendors must provide accurate product information, fair pricing, safe and lawful goods, correct stock details, proper packaging, timely order preparation, and cooperation with refunds, reviews, and support requests.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Intellectual Property') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart names, logos, user interface, content, software, and platform materials are owned by DailyCart or licensed to DailyCart. Users may not copy, misuse, reverse engineer, or exploit platform materials without permission.') }}</p>
                        </article>

                        <article class="rounded-3xl bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-bold">{{ __('Limitation of Liability') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart will make reasonable efforts to provide reliable services, but we are not liable for indirect losses, delays outside our control, third-party provider issues, inaccurate user/vendor information, or events beyond reasonable control, to the extent permitted by applicable law.') }}</p>
                        </article>
                    </div>

                    <article class="rounded-3xl bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold">{{ __('Changes to Terms and Contact Details') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('DailyCart may update these Terms and Conditions when platform features, payment methods, delivery processes, or legal requirements change. Continued use of DailyCart after updates means you accept the latest terms.') }}</p>
                        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                            <div class="rounded-2xl bg-brand-light p-4"><span class="font-bold text-brand-dark">{{ __('Email') }}</span><p>uahsens1@gmail.com</p></div>
                            <div class="rounded-2xl bg-brand-light p-4"><span class="font-bold text-brand-dark">{{ __('Phone') }}</span><p>+94 75 460 3008</p></div>
                            <div class="rounded-2xl bg-brand-light p-4"><span class="font-bold text-brand-dark">{{ __('Address') }}</span><p>Batticaloa, Sri Lanka</p></div>
                        </div>
                    </article>
                </section>
            </div>
        </main>

        <x-footer />
    </body>
</html>
