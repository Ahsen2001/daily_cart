DAILYCART ACCOUNT UPDATE

Hello {{ $recipientName }},

{{ $subjectLine }}

{{ $bodyText }}

@if (filled($actionUrl) && filled($actionLabel))
{{ $actionLabel }}: {{ $actionUrl }}

@endif
Need help? {{ route('support.tickets.create') }}

DailyCart
Daily essentials, delivered smart.
