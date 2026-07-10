<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ __('Edit Brand') }}</h2></x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                @include('admin.brands._form')
                <div class="mt-6 flex gap-3">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a class="dc-button-secondary" href="{{ route('admin.brands.index') }}">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
