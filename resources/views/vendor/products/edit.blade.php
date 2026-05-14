<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Product') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data" class="p-6 bg-white shadow-sm sm:rounded-lg">
                @csrf
                @method('PUT')
                @include('vendor.products._form', ['product' => $product])
                <div class="flex justify-end mt-6">
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
