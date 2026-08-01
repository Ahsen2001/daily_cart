@php
    use App\Services\CurrencyService;
    $canUpload = in_array($transfer->status, ['pending_upload', 'rejected'], true);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Bank transfer payment</h2>
                <p class="text-sm text-gray-500">{{ $payment->order->order_number }}</p>
            </div>
            <a href="{{ route('customer.payments.show', $payment->order) }}" class="text-sm font-semibold text-green-700 underline">Back to payment</a>
        </div>
    </x-slot>

    <div class="bg-[#F4FFF7] py-8 sm:py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-white p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[1.15fr_.85fr]">
                <section class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-green-700">DailyCart bank transfer</p>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Transfer {{ CurrencyService::formatLkr($transfer->expected_amount) }}</h1>
                    <p class="mt-2 text-sm text-gray-600">Use the reference below exactly. Your order is sent to the vendor only after a finance reviewer verifies the slip.</p>

                    <dl class="mt-6 divide-y divide-gray-100 rounded-2xl border border-green-100 bg-green-50/50 px-5">
                        <div class="py-4"><dt class="text-xs font-bold uppercase text-gray-500">Bank name</dt><dd class="mt-1 font-semibold text-gray-900">{{ $transfer->bank_name }}</dd></div>
                        <div class="py-4"><dt class="text-xs font-bold uppercase text-gray-500">Account holder</dt><dd class="mt-1 font-semibold text-gray-900">{{ $transfer->account_holder }}</dd></div>
                        <div class="py-4"><dt class="text-xs font-bold uppercase text-gray-500">Account number</dt><dd class="mt-1 break-all font-semibold text-gray-900">{{ $transfer->account_number }}</dd></div>
                        @if ($transfer->branch)<div class="py-4"><dt class="text-xs font-bold uppercase text-gray-500">Branch</dt><dd class="mt-1 font-semibold text-gray-900">{{ $transfer->branch }}</dd></div>@endif
                        <div class="py-4"><dt class="text-xs font-bold uppercase text-gray-500">Payment reference</dt><dd class="mt-1 break-all text-lg font-bold text-green-700">{{ $transfer->reference_number }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div><h2 class="text-lg font-bold text-gray-900">Payment slip</h2><p class="mt-1 text-sm text-gray-500">JPG, JPEG, PNG, or PDF up to 20 MB.</p></div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $transfer->isVerified() ? 'bg-green-100 text-green-700' : ($transfer->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700') }}">{{ str_replace('_', ' ', $transfer->status) }}</span>
                    </div>

                    @if ($transfer->last_rejection_reason)
                        <div class="mt-5 rounded-2xl bg-red-50 p-4 text-sm text-red-800"><strong>Reviewer note:</strong> {{ $transfer->last_rejection_reason }}</div>
                    @endif

                    @if ($canUpload)
                        <form method="POST" action="{{ route('customer.payments.bank-transfer.slips.store', $payment) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                            @csrf
                            <label class="block text-sm font-semibold text-gray-800" for="slip">Upload payment slip</label>
                            <input id="slip" name="slip" type="file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm">
                            <button type="submit" class="w-full rounded-full bg-green-600 px-5 py-3 text-sm font-bold text-white hover:bg-green-700">Submit for verification</button>
                        </form>
                    @elseif ($transfer->isVerified())
                        <div class="mt-6 rounded-2xl bg-green-50 p-4 text-sm text-green-800">Payment verified. Your vendor can now confirm the order.</div>
                    @else
                        <div class="mt-6 rounded-2xl bg-orange-50 p-4 text-sm text-orange-800">Your slip is securely stored and is awaiting a finance review.</div>
                    @endif

                    @if ($transfer->slips->isNotEmpty())
                        <p class="mt-5 text-xs text-gray-500">Latest uploaded slip: {{ $transfer->slips->sortByDesc('uploaded_at')->first()->original_name }} · {{ $transfer->slips->sortByDesc('uploaded_at')->first()->uploaded_at?->format('d M Y, h:i A') }}</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
