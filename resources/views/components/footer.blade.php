<footer class="border-t border-green-100 bg-white">
    <div class="dc-container py-12">
        <div class="grid gap-8 lg:grid-cols-4">
            <div class="space-y-4">
                <x-application-logo />
                <p class="text-sm leading-6 text-brand-text/70">DailyCart is a smart online shopping and daily essentials delivery platform built for fast, reliable local delivery.</p>
                <div class="flex flex-wrap gap-2 text-xs font-bold text-brand-dark">
                    <span class="rounded-full bg-brand-light px-3 py-2">{{ __('Local vendors') }}</span>
                    <span class="rounded-full bg-brand-light px-3 py-2">{{ __('Secure checkout') }}</span>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-brand-text">{{ __('Contact') }}</h3>
                <div class="mt-4 space-y-2 text-sm text-brand-text/70">
                    <p>Email: {{ config('mail.from.address') }}</p>
                    <p>Phone: +94 75 460 3008</p>
                    <p>Address: Batticaloa, Sri Lanka</p>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-brand-text">{{ __('Quick Links') }}</h3>
                <div class="mt-4 grid gap-2 text-sm text-brand-text/70">
                    <a class="hover:text-brand-dark" href="{{ route('products.index') }}">{{ __('Products') }}</a>
                    <a class="hover:text-brand-dark" href="{{ route('pages.about') }}">{{ __('About') }}</a>
                    <a class="hover:text-brand-dark" href="{{ route('pages.offers') }}">{{ __('Offers') }}</a>
                    <a class="hover:text-brand-dark" href="{{ route('pages.contact') }}">{{ __('Contact') }}</a>
                    <a class="hover:text-brand-dark" href="{{ route('vendor.register') }}">{{ __('Become a Vendor') }}</a>
                    <a class="hover:text-brand-dark" href="{{ route('rider.register') }}">{{ __('Become a Rider') }}</a>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <h3 class="font-bold text-brand-text">{{ __('Policies') }}</h3>
                    <div class="mt-4 grid gap-2 text-sm text-brand-text/70">
                        <a class="hover:text-brand-dark" href="{{ route('pages.refund-policy') }}">{{ __('Refund Policy') }}</a>
                        <a class="hover:text-brand-dark" href="{{ route('pages.privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                        <a class="hover:text-brand-dark" href="{{ route('pages.terms-and-conditions') }}">{{ __('Terms and Conditions') }}</a>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-brand-text">{{ __('Download App') }}</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-2xl bg-brand-text px-4 py-3 text-sm font-semibold text-white">{{ __('Mobile apps coming soon') }}</span>
                    </div>
                </div>
                <form class="rounded-3xl bg-brand-light p-4" method="POST" action="{{ route('newsletter.subscribe') }}">
                    @csrf
                    <label class="text-sm font-semibold text-brand-text">{{ __('Newsletter') }}</label>
                    <div class="mt-3 flex gap-2">
                        <input class="min-w-0 flex-1 rounded-full border-white bg-white text-sm" type="email" name="email" value="{{ old('email') }}" placeholder="Email address" required>
                        <button class="rounded-full bg-brand-orange px-4 text-sm font-bold text-white transition hover:bg-orange-600" type="submit">{{ __('Join') }}</button>
                    </div>
                    @if (session('newsletter_status'))
                        <p class="mt-3 text-sm font-medium text-brand-dark">{{ session('newsletter_status') }}</p>
                    @endif
                    @error('email')
                        <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>
        <div class="mt-10 border-t border-green-100 pt-6 text-center text-sm text-brand-text/60">
            {{ __('Copyright') }} {{ now()->year }} DailyCart. {{ __('All rights reserved.') }}
        </div>
    </div>
</footer>
