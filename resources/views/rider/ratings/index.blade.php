<x-app-layout>
    <x-slot name="header"><div><p class="dc-page-eyebrow">{{ __('Delivery feedback') }}</p><h2 class="dc-page-title">{{ __('My Ratings') }}</h2></div></x-slot>
    <div class="dc-page-section">
        <div class="dc-container space-y-6">
            @if (session('status'))<div class="dc-card p-4 text-sm font-semibold text-brand-primary">{{ session('status') }}</div>@endif
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="dc-card p-5"><p class="dc-page-eyebrow">{{ __('Average') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($statistics['average'], 1) }}<span class="text-base text-brand-muted">/5</span></p></div>
                <div class="dc-card p-5"><p class="dc-page-eyebrow">{{ __('Visible ratings') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($statistics['count']) }}</p></div>
                <div class="dc-card p-5"><p class="dc-page-eyebrow">{{ __('Five-star ratings') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($statistics['distribution'][5]) }}</p></div>
                <div class="dc-card p-5"><p class="dc-page-eyebrow">{{ __('Under review') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($statistics['reported']) }}</p></div>
            </section>
            <section class="dc-card overflow-hidden">
                <div class="border-b border-brand-border p-6"><h3 class="text-lg font-extrabold">{{ __('Customer feedback') }}</h3><p class="mt-1 text-sm text-brand-muted">{{ __('Customer identities are private. Report abusive or unrelated comments for administrator review.') }}</p></div>
                <div class="divide-y divide-brand-border">
                    @forelse ($ratings as $rating)
                    <article class="p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div><p class="font-extrabold text-amber-500">{{ str_repeat('★', $rating->rating) }}<span class="text-gray-200">{{ str_repeat('★', 5 - $rating->rating) }}</span></p><p class="mt-1 text-xs text-brand-muted">{{ __('Order') }} {{ $rating->order?->order_number }} · <x-local-time :date="$rating->created_at" /></p></div>
                            <span class="w-fit rounded-full px-3 py-1 text-xs font-bold {{ $rating->status === 'reported' ? 'bg-orange-100 text-orange-700' : 'bg-green-50 text-green-700' }}">{{ ucfirst($rating->status) }}</span>
                        </div>
                        @if ($rating->tags)<div class="mt-3 flex flex-wrap gap-2">@foreach ($rating->tags as $tag)<span class="rounded-full bg-brand-light px-3 py-1 text-xs font-semibold">{{ __(str_replace('_', ' ', ucfirst($tag))) }}</span>@endforeach</div>@endif
                        @if ($rating->comment)<p class="mt-4 text-sm leading-7 text-brand-text/75">{{ $rating->comment }}</p>@endif
                        @if ($rating->status === 'visible' && $rating->comment)
                        <details class="mt-4"><summary class="cursor-pointer text-sm font-semibold text-orange-700">{{ __('Report this comment') }}</summary>
                            <form method="POST" action="{{ route('rider.ratings.report', $rating) }}" class="mt-3 flex flex-col gap-3 sm:flex-row">@csrf @method('PATCH')<input name="reason" class="w-full rounded-xl border-brand-border text-sm" maxlength="1000" required placeholder="{{ __('Explain why this comment requires review') }}"><button class="rounded-full bg-orange-500 px-5 py-2 text-sm font-bold text-white">{{ __('Report') }}</button></form>
                        </details>
                        @endif
                    </article>
                    @empty
                    <p class="p-8 text-center text-sm text-brand-muted">{{ __('No customer ratings yet.') }}</p>
                    @endforelse
                </div>
                @if ($ratings->hasPages())<div class="border-t border-brand-border p-5">{{ $ratings->links() }}</div>@endif
            </section>
        </div>
    </div>
</x-app-layout>
