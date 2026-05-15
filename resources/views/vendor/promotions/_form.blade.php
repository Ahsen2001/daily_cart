@csrf
@isset($method) @method($method) @endisset
<div class="grid gap-4 md:grid-cols-2">
    <div><x-input-label for="title" :value="__('Title')" /><x-text-input id="title" name="title" class="mt-1 w-full" :value="old('title', $promotion->title)" required /></div>
    <div><x-input-label for="promotion_type" :value="__('Promotion Type')" /><select id="promotion_type" name="promotion_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">@foreach(['flash_sale','seasonal_offer','featured_offer','clearance_sale'] as $type)<option value="{{ $type }}" @selected(old('promotion_type', $promotion->promotion_type) === $type)>{{ str_replace('_',' ',ucfirst($type)) }}</option>@endforeach</select></div>
    <div class="md:col-span-2"><x-input-label for="description" :value="__('Description')" /><textarea id="description" name="description" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $promotion->description) }}</textarea></div>
    <div><x-input-label for="target_type" :value="__('Target Type')" /><select id="target_type" name="target_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">@foreach(['product','category','vendor','global'] as $type)<option value="{{ $type }}" @selected(old('target_type', $promotion->target_type ?? 'global') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
    <div><x-input-label for="target_id" :value="__('Target ID')" /><x-text-input id="target_id" name="target_id" type="number" class="mt-1 w-full" :value="old('target_id', $promotion->target_id)" /></div>
    <div><x-input-label for="discount_type" :value="__('Discount Type')" /><select id="discount_type" name="discount_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">@foreach(['fixed_amount','percentage'] as $type)<option value="{{ $type }}" @selected(old('discount_type', $promotion->discount_type) === $type)>{{ str_replace('_',' ',ucfirst($type)) }}</option>@endforeach</select></div>
    <div><x-input-label for="discount_value" :value="__('Discount Value')" /><x-text-input id="discount_value" name="discount_value" type="number" step="0.01" class="mt-1 w-full" :value="old('discount_value', $promotion->discount_value)" required /></div>
    <div><x-input-label for="starts_at" :value="__('Starts At')" /><x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 w-full" :value="old('starts_at', optional($promotion->starts_at)->format('Y-m-d\TH:i'))" required /></div>
    <div><x-input-label for="ends_at" :value="__('Ends At')" /><x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 w-full" :value="old('ends_at', optional($promotion->ends_at)->format('Y-m-d\TH:i'))" required /></div>
    <div><x-input-label for="status" :value="__('Status')" /><select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">@foreach(['active','inactive','expired'] as $status)<option value="{{ $status }}" @selected(old('status', $promotion->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div><x-input-label for="banner_image" :value="__('Banner Image')" /><input id="banner_image" type="file" name="banner_image" class="mt-1 text-sm" accept="image/*"></div>
    @isset($vendors)
        <div><x-input-label for="vendor_id" :value="__('Vendor')" /><select id="vendor_id" name="vendor_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"><option value="">{{ __('Global') }}</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}" @selected(old('vendor_id', $promotion->vendor_id) == $vendor->id)>{{ $vendor->store_name }}</option>@endforeach</select></div>
    @endisset
</div>
<div class="mt-6"><x-primary-button>{{ __('Save Promotion') }}</x-primary-button></div>
