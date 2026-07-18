<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Delivery Analytics') }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto grid max-w-6xl gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:px-6 lg:px-8">@foreach($summary as $key => $value)<div class="rounded-lg bg-white p-5 shadow"><p class="text-xs uppercase text-gray-500">{{ __(str_replace('_',' ',$key)) }}</p><p class="mt-2 text-2xl font-bold">{{ $key === 'average_delivery_minutes' ? $value.' min' : number_format($value) }}</p></div>@endforeach</div></div>
</x-app-layout>
