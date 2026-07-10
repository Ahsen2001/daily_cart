<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Manage Public Pages') }}</h2>
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back to Dashboard') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($pages as $slug => $label)
                    <article class="rounded-3xl border border-green-100 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-dark">{{ __('Public Page') }}</p>
                        <h3 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __($label) }}</h3>
                        <p class="mt-3 text-sm leading-6 text-brand-text/65">
                            {{ __('Edit the title, body, contact details, and call-to-action shown to visitors.') }}
                        </p>
                        <div class="mt-5 flex gap-3">
                            <a class="dc-button" href="{{ route('admin.pages.edit', $slug) }}">{{ __('Edit') }}</a>
                            <a class="dc-button-secondary" href="{{ route('pages.'.$slug) }}" target="_blank">{{ __('View') }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
