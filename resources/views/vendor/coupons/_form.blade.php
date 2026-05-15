@csrf
@isset($method) @method($method) @endisset
<div class="grid gap-4 md:grid-cols-2">
    <div><x-input-label for="code" :value="__('Code')" /><x-text-input id="code" name="code" class="mt-1 w-full" :value="old('code', $coupon->code)" required /></div>
    <div><x-input-label for="title" :value="__('Title')" /><x-text-input id="title" name="title" class="mt-1 w-full" :value="old('title', $coupon->title)" required /></div>
    <div class="md:col-span-2"><x-input-label for="description" :value="__('Description')" /><textarea id="description" name="description" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $coupon->description) }}</textarea></div>
    <div><x-input-label for="discount_type" :value="__('Discount Type')" /><select id="discount_type" name="discount_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">@foreach(['fixed_amount','percentage','free_delivery'] as $type)<option value="{{ $type }}" @selected(old('discount_type', $coupon->discount_type) === $type)>{{ str_replace('_',' ',ucfirst($type)) }}</option>@endforeach</select></div>
    <div><x-input-label for="discount_value" :value="__('Discount Value')" /><x-text-input id="discount_value" name="discount_value" type="number" step="0.01" class="mt-1 w-full" :value="old('discount_value', $coupon->discount_value ?? $coupon->value ?? 0)" required /></div>
    <div><x-input-label for="minimum_order_amount" :value="__('Minimum Order')" /><x-text-input id="minimum_order_amount" name="minimum_order_amount" type="number" step="0.01" class="mt-1 w-full" :value="old('minimum_order_amount', $coupon->minimum_order_amount ?? 0)" required /></div>
    <div><x-input-label for="maximum_discount_amount" :value="__('Maximum Discount')" /><x-text-input id="maximum_discount_amount" name="maximum_discount_amount" type="number" step="0.01" class="mt-1 w-full" :value="old('maximum_discount_amount', $coupon->maximum_discount_amount ?? $coupon->max_discount_amount)" /></div>
    <div><x-input-label for="usage_limit" :value="__('Usage Limit')" /><x-text-input id="usage_limit" name="usage_limit" type="number" class="mt-1 w-full" :value="old('usage_limit', $coupon->usage_limit)" /></div>
    <div><x-input-label for="per_customer_limit" :value="__('Per Customer Limit')" /><x-text-input id="per_customer_limit" name="per_customer_limit" type="number" class="mt-1 w-full" :value="old('per_customer_limit', $coupon->per_customer_limit)" /></div>
    <div><x-input-label for="starts_at" :value="__('Starts At')" /><x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 w-full" :value="old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i'))" required /></div>
    <div><x-input-label for="expires_at" :value="__('Expires At')" /><x-text-input id="expires_at" name="expires_at" type="datetime-local" class="mt-1 w-full" :value="old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i'))" required /></div>
    <div><x-input-label for="status" :value="__('Status')" /><select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">@foreach(['active','inactive','expired'] as $status)<option value="{{ $status }}" @selected(old('status', $coupon->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
    @isset($vendors)
        <div><x-input-label for="vendor_id" :value="__('Vendor')" /><select id="vendor_id" name="vendor_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"><option value="">{{ __('Global') }}</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}" @selected(old('vendor_id', $coupon->vendor_id) == $vendor->id)>{{ $vendor->store_name }}</option>@endforeach</select></div>
    @endisset
</div>
<div class="mt-6"><x-primary-button>{{ __('Save Coupon') }}</x-primary-button></div>
