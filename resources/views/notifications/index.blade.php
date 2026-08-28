<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Notifications') }}</h2></x-slot>
    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))<div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 ring-1 ring-emerald-100">{{ session('status') }}</div>@endif
            @include('notifications._action-center', ['adminCenter' => false])
        </div>
    </div>
</x-app-layout>
