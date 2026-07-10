<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit :page Page', ['page' => $pageLabel]) }}</h2>
            <a href="{{ route('admin.pages.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('All Pages') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-6 rounded-3xl border border-green-100 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="page_{{ $page }}_title" :value="__('Title')" />
                    <x-text-input id="page_{{ $page }}_title" name="page_{{ $page }}_title" class="mt-1 block w-full" :value="old('page_'.$page.'_title', $content['page_'.$page.'_title'])" required />
                    <x-input-error :messages="$errors->get('page_'.$page.'_title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="page_{{ $page }}_subtitle" :value="__('Subtitle')" />
                    <x-text-input id="page_{{ $page }}_subtitle" name="page_{{ $page }}_subtitle" class="mt-1 block w-full" :value="old('page_'.$page.'_subtitle', $content['page_'.$page.'_subtitle'])" />
                    <x-input-error :messages="$errors->get('page_'.$page.'_subtitle')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="page_{{ $page }}_body" :value="__('Body Content')" />
                    <textarea id="page_{{ $page }}_body" name="page_{{ $page }}_body" rows="8" class="mt-1 block w-full rounded-2xl border-gray-200 shadow-sm focus:border-brand-primary focus:ring-brand-primary" required>{{ old('page_'.$page.'_body', $content['page_'.$page.'_body']) }}</textarea>
                    <x-input-error :messages="$errors->get('page_'.$page.'_body')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="page_{{ $page }}_email" :value="__('Email')" />
                        <x-text-input id="page_{{ $page }}_email" name="page_{{ $page }}_email" type="email" class="mt-1 block w-full" :value="old('page_'.$page.'_email', $content['page_'.$page.'_email'])" />
                        <x-input-error :messages="$errors->get('page_'.$page.'_email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="page_{{ $page }}_phone" :value="__('Phone')" />
                        <x-text-input id="page_{{ $page }}_phone" name="page_{{ $page }}_phone" class="mt-1 block w-full" :value="old('page_'.$page.'_phone', $content['page_'.$page.'_phone'])" />
                        <x-input-error :messages="$errors->get('page_'.$page.'_phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="page_{{ $page }}_address" :value="__('Address')" />
                        <x-text-input id="page_{{ $page }}_address" name="page_{{ $page }}_address" class="mt-1 block w-full" :value="old('page_'.$page.'_address', $content['page_'.$page.'_address'])" />
                        <x-input-error :messages="$errors->get('page_'.$page.'_address')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="page_{{ $page }}_cta_label" :value="__('Button Label')" />
                        <x-text-input id="page_{{ $page }}_cta_label" name="page_{{ $page }}_cta_label" class="mt-1 block w-full" :value="old('page_'.$page.'_cta_label', $content['page_'.$page.'_cta_label'])" />
                        <x-input-error :messages="$errors->get('page_'.$page.'_cta_label')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="page_{{ $page }}_cta_url" :value="__('Button URL')" />
                        <x-text-input id="page_{{ $page }}_cta_url" name="page_{{ $page }}_cta_url" class="mt-1 block w-full" :value="old('page_'.$page.'_cta_url', $content['page_'.$page.'_cta_url'])" />
                        <x-input-error :messages="$errors->get('page_'.$page.'_cta_url')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a class="dc-button-secondary" href="{{ route('pages.'.$page) }}" target="_blank">{{ __('Preview') }}</a>
                    <button class="dc-button" type="submit">{{ __('Save Page') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
