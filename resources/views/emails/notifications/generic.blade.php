<p>{{ $body }}</p>

@if ($details !== [])
    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:640px; margin:20px 0;">
        @foreach ($details as $label => $value)
            <tr>
                <th align="left" style="padding:8px 12px 8px 0; vertical-align:top; color:#4b5563; font-weight:600; white-space:nowrap;">{{ $label }}</th>
                <td style="padding:8px 0; color:#111827;">{{ $value }}</td>
            </tr>
        @endforeach
    </table>
@endif

@if (filled($actionUrl) && filled($actionLabel))
    <p style="margin-top:24px;">
        <a href="{{ $actionUrl }}" style="display:inline-block; background:#15803d; color:#ffffff; padding:12px 18px; border-radius:6px; text-decoration:none; font-weight:600;">{{ $actionLabel }}</a>
    </p>
@endif
