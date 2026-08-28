@php
use App\Services\CurrencyService;
$order->loadMissing(['customer.user', 'vendor', 'items', 'payment', 'delivery.rider.user']);
@endphp
DAILYCART RECEIPT

Order: {{ $order->order_number }}
Customer: {{ $order->customer?->user?->name ?? 'Customer' }}
Store: {{ $order->vendor?->store_name ?? 'DailyCart partner store' }}
Delivery address: {{ $order->delivery_address }}

ITEMS
@foreach ($order->items as $item)
{{ $item->product_name }} — {{ $item->quantity }} × {{ CurrencyService::formatLkr($item->unit_price) }} = {{ CurrencyService::formatLkr($item->total_price) }}
@endforeach

Subtotal: {{ CurrencyService::formatLkr($order->subtotal) }}
@if ((float) $order->discount_amount > 0)
Discount: -{{ CurrencyService::formatLkr($order->discount_amount) }}
@endif
@if ((float) $order->loyalty_discount_amount > 0)
Loyalty discount: -{{ CurrencyService::formatLkr($order->loyalty_discount_amount) }}
@endif
Delivery fee: {{ CurrencyService::formatLkr($order->delivery_fee) }}
Service charge: {{ CurrencyService::formatLkr($order->service_charge) }}
@if ((float) $order->tax_amount > 0)
Tax: {{ CurrencyService::formatLkr($order->tax_amount) }}
@endif
Grand total: {{ CurrencyService::formatLkr($order->total_amount) }}

Payment method: {{ str_replace('_', ' ', $order->payment?->payment_method ?? 'pending') }}
Payment status: {{ str_replace('_', ' ', $order->payment?->status ?? $order->payment_status) }}
@if ($order->payment?->transaction_reference)
Reference: {{ $order->payment->transaction_reference }}
@endif
@if ($order->delivery?->rider?->user)
Delivered by: {{ $order->delivery->rider->user->name }}
@endif

View order: {{ route('customer.orders.show', $order) }}
Support: {{ route('support.tickets.create') }}
Refund policy: {{ route('pages.refund-policy') }}

Thank you for shopping with DailyCart.
