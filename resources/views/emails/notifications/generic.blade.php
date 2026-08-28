@php
    $normalizedRole = strtolower($role);
    $roleLabel = match ($normalizedRole) {
        'super admin' => 'Super Admin',
        'admin' => 'Admin',
        'vendor' => 'Vendor',
        'rider' => 'Rider',
        default => 'Customer',
    };
    $palette = match ($normalizedRole) {
        'rider' => ['#1e1b4b', '#4338ca', '#e0e7ff', '#3730a3'],
        'admin', 'super admin' => ['#0f172a', '#047857', '#d1fae5', '#065f46'],
        'vendor' => ['#052e2b', '#0f766e', '#ccfbf1', '#115e59'],
        default => ['#052e16', '#15803d', '#dcfce7', '#166534'],
    };
    $semantic = match ($tone) {
        'success' => ['#ecfdf3', '#166534', '#22c55e', 'Completed'],
        'warning' => ['#fffbeb', '#92400e', '#f59e0b', 'Attention'],
        'danger' => ['#fff1f2', '#9f1239', '#f43f5e', 'Important'],
        default => ['#eff6ff', '#1d4ed8', '#3b82f6', 'New update'],
    };
@endphp

<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $title }} — {{ $body }}</div>
<div style="margin:0;padding:24px 12px;background:#f4f8f5;font-family:Arial,Helvetica,sans-serif;color:#18231c;line-height:1.55;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:680px;margin:0 auto;border-collapse:separate;">
        <tr>
            <td style="overflow:hidden;border-radius:24px 24px 0 0;background:{{ $palette[0] }};color:#ffffff;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:24px 30px;font-size:23px;font-weight:800;letter-spacing:-.03em;">DailyCart<span style="color:#5ee49a;">.</span></td>
                        <td style="padding:24px 30px;text-align:right;"><span style="display:inline-block;padding:7px 11px;border:1px solid rgba(255,255,255,.18);border-radius:999px;background:rgba(255,255,255,.10);font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">{{ $roleLabel }}</span></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:8px 30px 30px;background:linear-gradient(135deg,{{ $palette[0] }},{{ $palette[1] }});">
                            <div style="font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#b9f3cf;">{{ $category }}</div>
                            <h1 style="margin:9px 0 0;font-size:28px;line-height:1.22;letter-spacing:-.03em;color:#ffffff;">{{ $title }}</h1>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:30px;border:1px solid #dce9df;border-top:0;border-radius:0 0 24px 24px;background:#ffffff;box-shadow:0 10px 30px rgba(18,55,31,.06);">
                <p style="margin:0 0 18px;font-size:15px;color:#526158;">Hello <strong style="color:#17251c;">{{ $recipientName ?: 'there' }}</strong>,</p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;border-collapse:separate;">
                    <tr>
                        <td width="6" style="border-radius:12px 0 0 12px;background:{{ $semantic[2] }};"></td>
                        <td style="padding:16px 18px;border-radius:0 12px 12px 0;background:{{ $semantic[0] }};color:{{ $semantic[1] }};">
                            <div style="margin-bottom:5px;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">{{ $semantic[3] }}</div>
                            <div style="font-size:15px;line-height:1.6;">{{ $body }}</div>
                        </td>
                    </tr>
                </table>

                @if ($details !== [])
                    <div style="margin:0 0 24px;overflow:hidden;border:1px solid #e1ebe4;border-radius:14px;">
                        <div style="padding:11px 15px;background:#f4f8f5;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#647168;">Update details</div>
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;font-size:13px;">
                            @foreach ($details as $label => $value)
                                <tr>
                                    <th align="left" width="42%" style="padding:11px 15px;border-top:1px solid #edf2ee;color:#66736a;font-size:11px;font-weight:700;text-transform:uppercase;vertical-align:top;">{{ $label }}</th>
                                    <td style="padding:11px 15px;border-top:1px solid #edf2ee;color:#1d2b22;font-weight:700;word-break:break-word;">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif

                @if (filled($actionUrl) && filled($actionLabel))
                    <div style="margin:26px 0;text-align:center;">
                        <a href="{{ $actionUrl }}" style="display:inline-block;padding:13px 22px;border-radius:12px;background:{{ $palette[1] }};color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;box-shadow:0 6px 16px rgba(21,128,61,.18);">{{ $actionLabel }} &nbsp;→</a>
                    </div>
                @endif

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;border-collapse:separate;">
                    <tr>
                        <td style="padding:15px 17px;border-radius:12px;background:#f8faf9;color:#647168;font-size:12px;line-height:1.6;">
                            <strong style="color:#33443a;">Stay in control.</strong><br>
                            Review this and your other updates in the
                            @if (filled($notificationCenterUrl))
                                <a href="{{ $notificationCenterUrl }}" style="color:{{ $palette[1] }};font-weight:700;text-decoration:none;">DailyCart notification center</a>
                            @else
                                DailyCart notification center
                            @endif
                            .
                        </td>
                    </tr>
                </table>

                <div style="margin-top:26px;padding-top:20px;border-top:1px solid #e5ede7;text-align:center;color:#7a867e;font-size:11px;line-height:1.7;">
                    <p style="margin:0;">Need assistance? <a href="{{ route('support.tickets.create') }}" style="color:{{ $palette[1] }};font-weight:700;text-decoration:none;">Contact DailyCart support</a></p>
                    <p style="margin:7px 0 0;">This is an automated service notification for your DailyCart account.</p>
                    <p style="margin:7px 0 0;font-weight:700;color:#506057;">Daily essentials, delivered smart.</p>
                </div>
            </td>
        </tr>
    </table>
</div>
