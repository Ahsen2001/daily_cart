@php
    $category = $category ?? null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" class="block w-full mt-1" :value="old('name', $category?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="slug" :value="__('Slug')" />
        <x-text-input id="slug" name="slug" class="block w-full mt-1" :value="old('slug', $category?->slug)" />
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="active" @selected(old('status', $category?->status ?? 'active') === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected(old('status', $category?->status) === 'inactive')>{{ __('Inactive') }}</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="image" :value="__('Image')" />
        <input id="image" type="file" name="image" class="block w-full mt-1 text-sm text-gray-700" accept="image/*" />
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="4" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>
