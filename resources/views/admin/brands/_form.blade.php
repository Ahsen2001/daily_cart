<div class="space-y-4">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $brand->name)" required />
    </div>
    <div>
        <x-input-label for="slug" :value="__('Slug')" />
        <x-text-input id="slug" name="slug" class="mt-1 block w-full" :value="old('slug', $brand->slug)" />
    </div>
    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="active" @selected(old('status', $brand->status ?: 'active') === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected(old('status', $brand->status) === 'inactive')>{{ __('Inactive') }}</option>
        </select>
    </div>
    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $brand->description) }}</textarea>
    </div>
</div>
