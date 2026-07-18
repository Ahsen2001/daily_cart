@php
    use App\Services\CurrencyService;
@endphp

<div style="max-width: 680px; margin: 0 auto; font-family: Arial, sans-serif; color: #27312b; line-height: 1.55;">
    <div style="padding: 28px; background: #0f7a3a; color: #ffffff; border-radius: 18px 18px 0 0;">
        <p style="margin: 0; font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">DailyCart invoice</p>
        <h1 style="margin: 8px 0 0; font-size: 26px;">Your order is out for delivery</h1>
        <p style="margin: 8px 0 0; opacity: .9;">Invoice {{ $order->order_number }}</p>
    </div>

    <div style="padding: 28px; border: 1px solid #dceee1; border-top: 0; border-radius: 0 0 18px 18px; background: #ffffff;">
        <p>Hello {{ $order->customer?->user?->name }},</p>
        <p>Your rider is on the way. Here is your itemized invoice for this delivery.</p>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin: 22px 0; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #f1faf4; color: #1e5b35; text-align: left;">
                    <th style="padding: 12px;">Item</th>
                    <th style="padding: 12px; text-align: right;">Qty</th>
                    <th style="padding: 12px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr style="border-bottom: 1px solid #e9f2ec;">
                        <td style="padding: 12px;">{{ $item->product_name }}</td>
                        <td style="padding: 12px; text-align: right;">{{ $item->quantity }}</td>
                        <td style="padding: 12px; text-align: right;">{{ CurrencyService::formatLkr($item->total_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px;">
            <tr><td style="padding: 5px 0;">Subtotal</td><td style="padding: 5px 0; text-align: right;">{{ CurrencyService::formatLkr($order->subtotal) }}</td></tr>
            @if ((float) $order->discount_amount > 0)
                <tr><td style="padding: 5px 0;">Discount</td><td style="padding: 5px 0; text-align: right;">−{{ CurrencyService::formatLkr($order->discount_amount) }}</td></tr>
            @endif
            @if ((float) $order->loyalty_discount_amount > 0)
                <tr><td style="padding: 5px 0;">Loyalty discount</td><td style="padding: 5px 0; text-align: right;">−{{ CurrencyService::formatLkr($order->loyalty_discount_amount) }}</td></tr>
            @endif
            <tr><td style="padding: 5px 0;">Delivery fee</td><td style="padding: 5px 0; text-align: right;">{{ CurrencyService::formatLkr($order->delivery_fee) }}</td></tr>
            <tr><td style="padding: 5px 0;">Service charge</td><td style="padding: 5px 0; text-align: right;">{{ CurrencyService::formatLkr($order->service_charge) }}</td></tr>
            <tr style="font-size: 18px; font-weight: 700;"><td style="padding-top: 14px; border-top: 1px solid #bcdcc5;">Total</td><td style="padding-top: 14px; border-top: 1px solid #bcdcc5; text-align: right;">{{ CurrencyService::formatLkr($order->total_amount) }}</td></tr>
        </table>

        <p style="margin: 24px 0 0;"><strong>Delivery address:</strong><br>{{ $order->delivery_address }}</p>
        @if ($order->delivery?->rider?->user)
            <p style="margin: 12px 0 0;"><strong>Rider:</strong> {{ $order->delivery->rider->user->name }}</p>
        @endif
        <p style="margin: 24px 0 0; color: #5f6d64; font-size: 13px;">Thank you for shopping with DailyCart.</p>
    </div>
</div>
