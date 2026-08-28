DAILYCART — {{ strtoupper($category) }}

Hello {{ $recipientName ?: 'there' }},

{{ $title }}

{{ $body }}

@if ($details !== [])
@foreach ($details as $label => $value)
{{ $label }}: {{ $value }}
@endforeach

@endif
@if (filled($actionUrl) && filled($actionLabel))
{{ $actionLabel }}: {{ $actionUrl }}

@endif
@if (filled($notificationCenterUrl))
Notification center: {{ $notificationCenterUrl }}

@endif
Need help? {{ route('support.tickets.create') }}

DailyCart
Daily essentials, delivered smart.
