<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Rider Rating Moderation') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ __('Average rating') }}</p><p class="mt-1 text-2xl font-bold">{{ number_format($statistics['average'], 1) }}/5</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ __('Visible ratings') }}</p><p class="mt-1 text-2xl font-bold">{{ number_format($statistics['count']) }}</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ __('Reported') }}</p><p class="mt-1 text-2xl font-bold">{{ number_format($statistics['reported']) }}</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ __('Five star') }}</p><p class="mt-1 text-2xl font-bold">{{ number_format($statistics['distribution'][5]) }}</p></div>
            </section>
            <form method="GET" class="grid gap-3 rounded-2xl bg-white p-5 shadow-sm sm:grid-cols-4">
                <select name="status" class="rounded-xl border-gray-200"><option value="">{{ __('All statuses') }}</option>@foreach (['visible','reported','hidden'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                <select name="rating" class="rounded-xl border-gray-200"><option value="">{{ __('All scores') }}</option>@foreach (range(5,1) as $score)<option value="{{ $score }}" @selected((string) request('rating') === (string) $score)>{{ $score }}/5</option>@endforeach</select>
                <select name="rider_id" class="rounded-xl border-gray-200"><option value="">{{ __('All riders') }}</option>@foreach ($riders as $rider)<option value="{{ $rider->id }}" @selected((string) request('rider_id') === (string) $rider->id)>{{ $rider->user?->name }}</option>@endforeach</select>
                <button class="rounded-full bg-green-600 px-5 py-2 text-sm font-bold text-white">{{ __('Filter') }}</button>
            </form>
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                @if (session('status'))<div class="border-b border-gray-100 p-4 text-sm font-semibold text-green-700">{{ session('status') }}</div>@endif
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Rider / Order') }}</th><th class="px-4 py-3">{{ __('Rating') }}</th><th class="px-4 py-3">{{ __('Feedback') }}</th><th class="px-4 py-3">{{ __('Status') }}</th><th class="px-4 py-3">{{ __('Moderate') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($ratings as $rating)
                        <tr class="align-top">
                            <td class="px-4 py-4"><p class="font-bold">{{ $rating->rider?->user?->name }}</p><p class="text-xs text-gray-500">{{ $rating->order?->order_number }} · {{ __('Customer') }} #{{ $rating->customer_id }}</p></td>
                            <td class="px-4 py-4 font-bold text-amber-500">{{ $rating->rating }}/5</td>
                            <td class="max-w-md px-4 py-4"><p>{{ $rating->comment ?: '—' }}</p>@if ($rating->report_reason)<p class="mt-2 rounded-lg bg-orange-50 p-2 text-xs text-orange-700"><strong>{{ __('Report:') }}</strong> {{ $rating->report_reason }}</p>@endif</td>
                            <td class="px-4 py-4">{{ ucfirst($rating->status) }}</td>
                            <td class="px-4 py-4"><form method="POST" action="{{ route('admin.rider-ratings.moderate', $rating) }}" class="flex gap-2">@csrf @method('PATCH')<select name="status" class="rounded-lg border-gray-200 text-xs"><option value="visible">{{ __('Visible') }}</option><option value="hidden" @selected($rating->status === 'hidden')>{{ __('Hidden') }}</option></select><button class="rounded-full border border-green-200 px-3 py-1 text-xs font-bold text-green-700">{{ __('Save') }}</button></form></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No rider ratings found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
                <div class="p-5">{{ $ratings->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
