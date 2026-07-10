<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ __('Create Brand') }}</h2></x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.brands.store') }}" class="rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @include('admin.brands._form')
                <div class="mt-6"><x-primary-button>{{ __('Save') }}</x-primary-button></div>
            </form>
        </div>
    </div>
</x-app-layout>
