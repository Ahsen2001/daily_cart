@props([
    'date' => null,
    'format' => 'datetime',
    'fallback' => '-',
])

@php
    $dateValue = $date instanceof \Carbon\CarbonInterface
        ? $date
        : ($date ? \Illuminate\Support\Carbon::parse($date) : null);
@endphp

@if ($dateValue)
    <time datetime="{{ $dateValue->copy()->utc()->toIso8601String() }}" data-local-time data-format="{{ $format }}">
        {{ $dateValue->format($format === 'date' ? 'M d, Y' : 'M d, Y h:i A') }}
    </time>
@else
    {{ $fallback }}
@endif

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const formatters = {
                datetime: {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                },
                seconds: {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                },
                short: {
                    month: 'short',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                },
                date: {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                },
                time: {
                    hour: '2-digit',
                    minute: '2-digit',
                },
            };

            document.querySelectorAll('[data-local-time]').forEach((element) => {
                const date = new Date(element.getAttribute('datetime'));
                const format = element.dataset.format || 'datetime';

                if (Number.isNaN(date.getTime())) {
                    return;
                }

                element.textContent = new Intl.DateTimeFormat(undefined, formatters[format] || formatters.datetime).format(date);
            });
        });
    </script>
@endonce
