<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Create Category') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="p-6 bg-white shadow-sm sm:rounded-lg">
                @csrf
                @include('admin.categories._form')
                <div class="flex justify-end mt-6">
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
