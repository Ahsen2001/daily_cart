@php
use App\Services\CurrencyService;
$order->loadMissing(['customer.user', 'vendor', 'items', 'payment', 'delivery.rider.user']);
$issuedAt = $order->payment?->paid_at ?? $order->delivery?->delivered_at ?? $order->updated_at;
$paymentMethod = str_replace('_', ' ', $order->payment?->payment_method ?? 'pending');
$paymentStatus = str_replace('_', ' ', $order->payment?->status ?? $order->payment_status);
@endphp

<div style="display:none;max-height:0;overflow:hidden;opacity:0;">Your receipt for DailyCart order {{ $order->order_number }} is ready.</div>
<div style="max-width: 700px; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; color: #17231c; line-height: 1.55; background:#f4faf6; padding:24px;">
    <div style="overflow:hidden; border-radius:24px 24px 0 0; background:#0b2e20; color:#ffffff;">
        <div style="padding:28px 32px 10px;">
            <table width="100%" cellpadding="0" cellspacing="0"><tr>
                <td style="font-size:23px;font-weight:800;letter-spacing:-.02em;">DailyCart<span style="color:#55d98b;">.</span></td>
                <td style="text-align:right;"><span style="display:inline-block;padding:7px 12px;border-radius:999px;background:#d9fbe6;color:#116633;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Delivered</span></td>
            </tr></table>
        </div>
        <div style="padding:14px 32px 32px; background:linear-gradient(135deg,#0b2e20,#0f7040);">
            <p style="margin:0;font-size:12px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#a7e9bf;">Payment receipt</p>
            <h1 style="margin:8px 0 0;font-size:30px;line-height:1.2;letter-spacing:-.03em;">Thanks for your order, {{ $order->customer?->user?->name ?? 'Customer' }}.</h1>
            <p style="margin:10px 0 0;color:#d6f4e0;">Everything is delivered. Here is your complete, itemized receipt.</p>
        </div>
    </div>

    <div style="padding:32px; border:1px solid #d8eadf; border-top:0; border-radius:0 0 24px 24px; background:#ffffff;">
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:26px;font-size:13px;">
            <tr>
                <td width="50%" valign="top" style="padding:14px 16px;background:#f3faf5;border-radius:14px 0 0 14px;">
                    <div style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#6a7b70;">Order number</div>
                    <div style="margin-top:4px;font-size:16px;font-weight:800;color:#153b27;">{{ $order->order_number }}</div>
                </td>
                <td width="50%" valign="top" style="padding:14px 16px;background:#f3faf5;border-left:1px solid #d8eadf;border-radius:0 14px 14px 0;">
                    <div style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#6a7b70;">Issued</div>
                    <div style="margin-top:4px;font-size:14px;font-weight:700;color:#153b27;">{{ $issuedAt?->format('M d, Y · h:i A') }}</div>
                </td>
            </tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:26px;font-size:13px;">
            <tr>
                <td width="50%" valign="top" style="padding-right:18px;">
                    <div style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#178147;">Delivered to</div>
                    <div style="margin-top:7px;font-weight:800;color:#17231c;">{{ $order->customer?->user?->name ?? 'Customer' }}</div>
                    <div style="margin-top:3px;color:#627068;">{{ $order->delivery_address }}</div>
                </td>
                <td width="50%" valign="top" style="padding-left:18px;border-left:1px solid #e3eee7;">
                    <div style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#178147;">Fulfilled by</div>
                    <div style="margin-top:7px;font-weight:800;color:#17231c;">{{ $order->vendor?->store_name ?? 'DailyCart partner store' }}</div>
                    @if ($order->vendor?->phone)<div style="margin-top:3px;color:#627068;">{{ $order->vendor->phone }}</div>@endif
                </td>
            </tr>
        </table>

        <h2 style="margin:0 0 10px;font-size:18px;letter-spacing:-.01em;">Order summary</h2>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#edf8f0;color:#315b40;text-align:left;">
                    <th style="padding:12px;border-radius:10px 0 0 10px;">Item</th>
                    <th style="padding:12px;text-align:right;">Unit price</th>
                    <th style="padding:12px;text-align:right;">Qty</th>
                    <th style="padding:12px;text-align:right;border-radius:0 10px 10px 0;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr style="border-bottom:1px solid #e9f2ec;">
                    <td style="padding:14px 12px;font-weight:700;color:#243229;">{{ $item->product_name }}</td>
                    <td style="padding:14px 12px;text-align:right;color:#627068;">{{ CurrencyService::formatLkr($item->unit_price) }}</td>
                    <td style="padding:14px 12px;text-align:right;color:#627068;">{{ $item->quantity }}</td>
                    <td style="padding:14px 12px;text-align:right;font-weight:700;">{{ CurrencyService::formatLkr($item->total_price) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px;">
            <tr>
                <td style="padding: 5px 0;">Subtotal</td>
                <td style="padding: 5px 0; text-align: right;">{{ CurrencyService::formatLkr($order->subtotal) }}</td>
            </tr>
            @if ((float) $order->discount_amount > 0)
            <tr>
                <td style="padding: 5px 0;">Discount</td>
                <td style="padding: 5px 0; text-align: right;">−{{ CurrencyService::formatLkr($order->discount_amount) }}</td>
            </tr>
            @endif
            @if ((float) $order->loyalty_discount_amount > 0)
            <tr>
                <td style="padding: 5px 0;">Loyalty discount</td>
                <td style="padding: 5px 0; text-align: right;">−{{ CurrencyService::formatLkr($order->loyalty_discount_amount) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 5px 0;">Delivery fee</td>
                <td style="padding: 5px 0; text-align: right;">{{ CurrencyService::formatLkr($order->delivery_fee) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0;">Service charge</td>
                <td style="padding: 5px 0; text-align: right;">{{ CurrencyService::formatLkr($order->service_charge) }}</td>
            </tr>
            @if ((float) $order->tax_amount > 0)
            <tr><td style="padding:5px 0;">Tax</td><td style="padding:5px 0;text-align:right;">{{ CurrencyService::formatLkr($order->tax_amount) }}</td></tr>
            @endif
            <tr style="font-size:19px;font-weight:800;color:#123a25;">
                <td style="padding-top:14px;border-top:2px solid #bcdcc5;">Grand total</td>
                <td style="padding-top:14px;border-top:2px solid #bcdcc5;text-align:right;">{{ CurrencyService::formatLkr($order->total_amount) }}</td>
            </tr>
        </table>

        <div style="margin-top:26px;padding:16px 18px;border-radius:14px;background:#f7faf8;border:1px solid #e0ece4;font-size:13px;">
            <table width="100%" cellpadding="0" cellspacing="0"><tr><td><strong>Payment</strong><br><span style="color:#627068;text-transform:capitalize;">{{ $paymentMethod }}</span></td><td style="text-align:right;"><span style="display:inline-block;padding:5px 10px;border-radius:999px;background:#dff7e7;color:#126b36;font-size:11px;font-weight:800;text-transform:uppercase;">{{ $paymentStatus }}</span></td></tr></table>
            @if ($order->payment?->transaction_reference)<div style="margin-top:10px;color:#627068;">Reference: <strong style="color:#33463a;">{{ $order->payment->transaction_reference }}</strong></div>@endif
        </div>

        @if ($order->delivery?->rider?->user)
        <p style="margin:18px 0 0;font-size:13px;color:#627068;"><strong style="color:#33463a;">Delivered by:</strong> {{ $order->delivery->rider->user->name }}</p>
        @endif

        <div style="margin-top:28px;text-align:center;">
            <a href="{{ route('customer.orders.show', $order) }}" style="display:inline-block;padding:13px 22px;border-radius:12px;background:#14783e;color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;">View order details</a>
        </div>

        <div style="margin-top:28px;padding-top:20px;border-top:1px solid #e3eee7;text-align:center;color:#748078;font-size:12px;">
            <p style="margin:0;">Need help? <a href="{{ route('support.tickets.create') }}" style="color:#14783e;font-weight:700;">Contact support</a> · <a href="{{ route('pages.refund-policy') }}" style="color:#14783e;font-weight:700;">Refund policy</a></p>
            <p style="margin:9px 0 0;">Thank you for shopping with DailyCart.</p>
        </div>
    </div>
</div>
