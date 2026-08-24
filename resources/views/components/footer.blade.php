<footer
    class="relative overflow-hidden border-t border-brand-border bg-gradient-to-br from-brand-light via-white to-emerald-50"
    aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">
        {{ __('DailyCart Footer') }}
    </h2>

    {{-- Decorative background --}}
    <div
        class="pointer-events-none absolute -left-32 -top-32 h-64 w-64 rounded-full bg-brand-primary/5 blur-3xl"
        aria-hidden="true"></div>

    <div
        class="pointer-events-none absolute -bottom-32 -right-24 h-72 w-72 rounded-full bg-brand-orange/5 blur-3xl"
        aria-hidden="true"></div>


    <div class="dc-container relative">

        {{-- =========================================================
             MAIN FOOTER
        ========================================================== --}}
        <div class="py-8 sm:py-10">

            <div class="dailycart-footer-grid">

                {{-- =====================================================
                     BRAND
                ====================================================== --}}
                <div class="dailycart-footer-brand">

                    <a
                        href="{{ url('/') }}"
                        class="inline-flex rounded-lg focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
                        aria-label="{{ __('DailyCart Home') }}">
                        <x-application-logo />
                    </a>

                    <p class="mt-3 max-w-md text-sm leading-6 text-brand-muted">
                        {{ __('DailyCart is a smart online shopping and daily essentials delivery platform built for fast, reliable local delivery.') }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2">

                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-muted">
                            <svg
                                class="h-4 w-4 text-brand-primary"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 12 4 4L19 6" />
                            </svg>

                            {{ __('Local Vendors') }}
                        </span>

                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-muted">
                            <svg
                                class="h-4 w-4 text-brand-primary"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <rect
                                    x="5"
                                    y="10"
                                    width="14"
                                    height="10"
                                    rx="2" />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>

                            {{ __('Secure Checkout') }}
                        </span>

                    </div>

                </div>


                {{-- =====================================================
                     CONTACT
                ====================================================== --}}
                <div class="dailycart-footer-contact">

                    <h3 class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-brand-dark">
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-brand-primary"
                            aria-hidden="true"></span>

                        {{ __('Contact') }}
                    </h3>

                    <div class="mt-4 space-y-3">

                        <a
                            href="mailto:{{ config('mail.from.address') }}"
                            class="flex items-start gap-2 text-sm text-brand-muted transition hover:text-brand-primary">
                            <svg
                                class="mt-0.5 h-4 w-4 shrink-0 text-brand-primary"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 7l9 6 9-6M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                            </svg>

                            <span class="break-all">
                                {{ config('mail.from.address') }}
                            </span>
                        </a>

                        <a
                            href="tel:+94754603008"
                            class="flex items-center gap-2 text-sm text-brand-muted transition hover:text-brand-primary">
                            <svg
                                class="h-4 w-4 shrink-0 text-brand-primary"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 4h4l2 5-3 2a15 15 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2C10 21 3 14 3 6a2 2 0 0 1 2-2Z" />
                            </svg>

                            <span>+94 75 460 3008</span>
                        </a>

                        <div class="flex items-start gap-2 text-sm text-brand-muted">
                            <svg
                                class="mt-0.5 h-4 w-4 shrink-0 text-brand-primary"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 21s7-5 7-12a7 7 0 1 0-14 0c0 7 7 12 7 12Z" />

                                <circle cx="12" cy="9" r="2" />
                            </svg>

                            <span>
                                {{ __('Batticaloa, Sri Lanka') }}
                            </span>
                        </div>

                    </div>
                </div>


                {{-- =====================================================
                     QUICK LINKS
                ====================================================== --}}
                <div class="dailycart-footer-links">

                    <h3 class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-brand-dark">
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-brand-primary"
                            aria-hidden="true"></span>

                        {{ __('Quick Links') }}
                    </h3>

                    <nav
                        class="mt-4 grid gap-1"
                        aria-label="{{ __('Quick Links') }}">
                        @foreach ([
                        [
                        'label' => __('Products'),
                        'route' => route('products.index')
                        ],
                        [
                        'label' => __('About'),
                        'route' => route('pages.about')
                        ],
                        [
                        'label' => __('Offers'),
                        'route' => route('pages.offers')
                        ],
                        [
                        'label' => __('Contact'),
                        'route' => route('pages.contact')
                        ],
                        [
                        'label' => __('Become a Vendor'),
                        'route' => route('vendor.register')
                        ],
                        [
                        'label' => __('Become a Rider'),
                        'route' => route('rider.register')
                        ],
                        ] as $link)

                        <a
                            href="{{ $link['route'] }}"
                            class="inline-flex min-h-7 w-fit items-center gap-2 text-sm text-brand-muted transition hover:translate-x-1 hover:text-brand-primary">
                            <span
                                class="h-1 w-1 rounded-full bg-brand-border"
                                aria-hidden="true"></span>

                            {{ $link['label'] }}
                        </a>

                        @endforeach
                    </nav>

                </div>


                {{-- =====================================================
                     POLICIES
                ====================================================== --}}
                <div class="dailycart-footer-policies">

                    <h3 class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-brand-dark">
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-brand-primary"
                            aria-hidden="true"></span>

                        {{ __('Policies') }}
                    </h3>

                    <nav
                        class="mt-4 grid gap-1"
                        aria-label="{{ __('Policies') }}">
                        @foreach ([
                        [
                        'label' => __('Refund Policy'),
                        'route' => route('pages.refund-policy')
                        ],
                        [
                        'label' => __('Privacy Policy'),
                        'route' => route('pages.privacy-policy')
                        ],
                        [
                        'label' => __('Terms and Conditions'),
                        'route' => route('pages.terms-and-conditions')
                        ],
                        ] as $link)

                        <a
                            href="{{ $link['route'] }}"
                            class="inline-flex min-h-7 w-fit items-center gap-2 text-sm text-brand-muted transition hover:translate-x-1 hover:text-brand-primary">
                            <span
                                class="h-1 w-1 rounded-full bg-brand-border"
                                aria-hidden="true"></span>

                            {{ $link['label'] }}
                        </a>

                        @endforeach
                    </nav>

                </div>

                {{-- Mobile Apps --}}
                <div class="grid-column: span 1">
                    <h3 class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-brand-dark">
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-brand-orange"
                            aria-hidden="true">
                        </span>

                        {{ __('Mobile Apps') }}
                    </h3>

                    <div class="mt-4 grid gap-2">

                        {{-- Android application --}}
                        <div class="flex items-center gap-3 rounded-xl border border-brand-border bg-white/80 px-3 py-2.5 shadow-sm">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 text-brand-primary">
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">

                                    <rect
                                        x="7"
                                        y="2"
                                        width="10"
                                        height="20"
                                        rx="2">
                                    </rect>

                                    <path
                                        stroke-linecap="round"
                                        d="M11 18h2">
                                    </path>
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-xs font-bold text-brand-dark">
                                    {{ __('Android App') }}
                                </p>

                                <p class="text-[11px] text-brand-muted">
                                    {{ __('Coming soon') }}
                                </p>
                            </div>
                        </div>

                        {{-- iOS application --}}
                        <div class="flex items-center gap-3 rounded-xl border border-brand-border bg-white/80 px-3 py-2.5 shadow-sm">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 text-brand-primary">
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">

                                    <rect
                                        x="7"
                                        y="2"
                                        width="10"
                                        height="20"
                                        rx="2">
                                    </rect>

                                    <path
                                        stroke-linecap="round"
                                        d="M11 18h2">
                                    </path>
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-xs font-bold text-brand-dark">
                                    {{ __('iOS App') }}
                                </p>

                                <p class="text-[11px] text-brand-muted">
                                    {{ __('Coming soon') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- =====================================================
                     NEWSLETTER
                ====================================================== --}}
                <div class="dailycart-footer-newsletter">

                    <div class="rounded-2xl border border-brand-primary/10 bg-white/80 p-4 shadow-sm">

                        <h3 class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-brand-dark">
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-brand-orange"
                                aria-hidden="true"></span>

                            {{ __('Newsletter') }}
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-brand-muted">
                            {{ __('Subscribe for exclusive offers and DailyCart updates.') }}
                        </p>

                        {{-- Existing newsletter logic preserved --}}
                        <form
                            method="POST"
                            action="{{ route('newsletter.subscribe') }}"
                            class="mt-3">
                            @csrf

                            <label
                                for="footer-newsletter-email"
                                class="sr-only">
                                {{ __('Email address') }}
                            </label>

                            <input
                                id="footer-newsletter-email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="{{ __('Email address') }}"
                                autocomplete="email"
                                required
                                class="h-10 w-full rounded-xl border-brand-border bg-white px-3.5 text-sm placeholder:text-brand-text/40 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10" />

                            <button
                                type="submit"
                                class="mt-2 inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-brand-orange px-4 text-sm font-bold text-white transition hover:bg-orange-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-orange/20">
                                {{ __('Join Newsletter') }}

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </button>

                            @if (session('newsletter_status'))
                            <p
                                class="mt-2 rounded-lg bg-brand-primary/10 px-3 py-2 text-xs font-medium text-brand-primary"
                                role="status">
                                {{ session('newsletter_status') }}
                            </p>
                            @endif

                            @error('email')
                            <p
                                class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-600"
                                role="alert">
                                {{ $message }}
                            </p>
                            @enderror

                        </form>

                    </div>

                </div>

            </div>
        </div>


        {{-- =========================================================
             FOOTER BOTTOM
        ========================================================== --}}
        <div class="border-t border-brand-border py-4">

            <div class="flex flex-col items-center justify-between gap-2 text-center sm:flex-row sm:text-left">

                <p class="text-xs text-brand-text/55">
                    <span class="font-semibold text-brand-dark">
                        {{ __('Copyright') }} © {{ now()->year }} DailyCart.
                    </span>

                    <span class="ml-1">
                        {{ __('All rights reserved.') }}
                    </span>
                </p>

                <p class="flex items-center gap-2 text-xs font-semibold text-brand-text/55">
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-brand-primary"
                        aria-hidden="true"></span>

                    {{ __('Daily essentials, delivered smart.') }}
                </p>

            </div>

        </div>

    </div>
</footer>
<style>
    .dailycart-footer-grid {
        display: grid;
        grid-template-columns: 2.2fr 1fr 1fr 1fr 1.4fr;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 1023px) {
        .dailycart-footer-grid {
            grid-template-columns: 2fr 1fr 1fr;
        }

        .dailycart-footer-brand {
            grid-column: span 3;
        }

        .dailycart-footer-newsletter {
            grid-column: span 3;
        }
    }

    @media (max-width: 767px) {
        .dailycart-footer-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.75rem 1.5rem;
        }

        .dailycart-footer-brand {
            grid-column: span 2;
        }

        .dailycart-footer-contact {
            grid-column: span 1;
        }

        .dailycart-footer-links {
            grid-column: span 1;
        }

        .dailycart-footer-policies {
            grid-column: span 1;
        }

        .dailycart-footer-newsletter {
            grid-column: span 1;
        }
    }

    @media (max-width: 479px) {
        .dailycart-footer-grid {
            grid-template-columns: 1fr;
            gap: 1.75rem;
        }

        .dailycart-footer-brand,
        .dailycart-footer-contact,
        .dailycart-footer-links,
        .dailycart-footer-policies,
        .dailycart-footer-newsletter {
            grid-column: span 1;
        }
    }
</style>