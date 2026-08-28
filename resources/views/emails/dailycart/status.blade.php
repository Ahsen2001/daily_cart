@php
    $normalizedRole = strtolower($role);
    $roleLabel = match ($normalizedRole) {
        'super admin' => 'Super Admin', 'admin' => 'Admin', 'vendor' => 'Vendor',
        'rider' => 'Rider', default => 'Customer',
    };
    $palette = match ($normalizedRole) {
        'rider' => ['#1e1b4b', '#4338ca'],
        'admin', 'super admin' => ['#0f172a', '#047857'],
        'vendor' => ['#052e2b', '#0f766e'],
        default => ['#052e16', '#15803d'],
    };
@endphp

<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $subjectLine }} — {{ $bodyText }}</div>
<div style="margin:0;padding:24px 12px;background:#f4f8f5;font-family:Arial,Helvetica,sans-serif;color:#18231c;line-height:1.55;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:660px;margin:0 auto;">
        <tr><td style="overflow:hidden;border-radius:24px 24px 0 0;background:{{ $palette[0] }};color:#ffffff;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr><td style="padding:24px 30px;font-size:23px;font-weight:800;">DailyCart<span style="color:#5ee49a;">.</span></td><td style="padding:24px 30px;text-align:right;"><span style="display:inline-block;padding:7px 11px;border:1px solid rgba(255,255,255,.18);border-radius:999px;background:rgba(255,255,255,.10);font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">{{ $roleLabel }}</span></td></tr>
                <tr><td colspan="2" style="padding:8px 30px 30px;background:linear-gradient(135deg,{{ $palette[0] }},{{ $palette[1] }});"><div style="font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#b9f3cf;">DailyCart update</div><h1 style="margin:9px 0 0;font-size:28px;line-height:1.22;color:#ffffff;">{{ $subjectLine }}</h1></td></tr>
            </table>
        </td></tr>
        <tr><td style="padding:30px;border:1px solid #dce9df;border-top:0;border-radius:0 0 24px 24px;background:#ffffff;">
            <p style="margin:0 0 18px;font-size:15px;color:#526158;">Hello <strong style="color:#17251c;">{{ $recipientName }}</strong>,</p>
            <div style="padding:18px 20px;border-left:5px solid {{ $palette[1] }};border-radius:0 12px 12px 0;background:#f3faf5;color:#33443a;font-size:15px;line-height:1.7;">{{ $bodyText }}</div>
            @if (filled($actionUrl) && filled($actionLabel))
                <div style="margin:28px 0;text-align:center;"><a href="{{ $actionUrl }}" style="display:inline-block;padding:13px 22px;border-radius:12px;background:{{ $palette[1] }};color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;">{{ $actionLabel }} &nbsp;→</a></div>
            @endif
            <div style="margin-top:26px;padding-top:20px;border-top:1px solid #e5ede7;text-align:center;color:#7a867e;font-size:11px;line-height:1.7;"><p style="margin:0;">Need assistance? <a href="{{ route('support.tickets.create') }}" style="color:{{ $palette[1] }};font-weight:700;text-decoration:none;">Contact DailyCart support</a></p><p style="margin:7px 0 0;">This is an automated account notification.</p><p style="margin:7px 0 0;font-weight:700;color:#506057;">Daily essentials, delivered smart.</p></div>
        </td></tr>
    </table>
</div>
