@php
    $product = $product ?? null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Product Name')" />
        <x-text-input id="name" class="block w-full mt-1" name="name" :value="old('name', $product?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="category_id" :value="__('Category')" />
        <select id="category_id" name="category_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $product?->category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="brand" :value="__('Brand')" />
        <x-text-input id="brand" class="block w-full mt-1" name="brand" :value="old('brand', $product?->brand)" />
        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="slug" :value="__('Slug')" />
        <x-text-input id="slug" class="block w-full mt-1" name="slug" :value="old('slug', $product?->slug)" />
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="price" :value="__('Price')" />
        <x-text-input id="price" class="block w-full mt-1" type="number" step="0.01" min="0" name="price" :value="old('price', $product?->price)" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="discount_price" :value="__('Discount Price')" />
        <x-text-input id="discount_price" class="block w-full mt-1" type="number" step="0.01" min="0" name="discount_price" :value="old('discount_price', $product?->discount_price)" />
        <x-input-error :messages="$errors->get('discount_price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="unit_type" :value="__('Unit Type')" />
        <x-text-input id="unit_type" class="block w-full mt-1" name="unit_type" :value="old('unit_type', $product?->unit_type ?? 'item')" required />
        <x-input-error :messages="$errors->get('unit_type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="weight" :value="__('Weight')" />
        <x-text-input id="weight" class="block w-full mt-1" name="weight" :value="old('weight', $product?->weight)" />
        <x-input-error :messages="$errors->get('weight')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="sku" :value="__('SKU')" />
        <x-text-input id="sku" class="block w-full mt-1" name="sku" :value="old('sku', $product?->sku)" />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="barcode" :value="__('Barcode')" />
        <x-text-input id="barcode" class="block w-full mt-1" name="barcode" :value="old('barcode', $product?->barcode)" />
        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="stock_quantity" :value="__('Stock Quantity')" />
        <x-text-input id="stock_quantity" class="block w-full mt-1" type="number" min="0" name="stock_quantity" :value="old('stock_quantity', $product?->stock_quantity ?? 0)" required />
        <x-input-error :messages="$errors->get('stock_quantity')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="expiry_date" :value="__('Expiry Date')" />
        <x-text-input id="expiry_date" class="block w-full mt-1" type="date" name="expiry_date" :value="old('expiry_date', optional($product?->expiry_date)->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="4" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $product?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="grid gap-4 mt-4 sm:grid-cols-2">
    <div>
        <x-input-label for="image" :value="__('Main Image')" />
        <input id="image" type="file" name="image" class="block w-full mt-1 text-sm text-gray-700" accept="image/*" />
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="images" :value="__('Gallery Images')" />
        <input id="images" type="file" name="images[]" class="block w-full mt-1 text-sm text-gray-700" accept="image/*" multiple />
        <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label :value="__('Variants')" />
    <div class="grid gap-2 mt-2 sm:grid-cols-4">
        @foreach ($variantExamples as $variant)
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="variants[]" value="{{ $variant }}" @checked(in_array($variant, old('variants', $product?->variants?->pluck('name')->all() ?? []), true))>
                <span>{{ $variant }}</span>
            </label>
        @endforeach
    </div>
</div>
