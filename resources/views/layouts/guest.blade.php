<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DailyCart') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-brand-text antialiased">
        <div class="min-h-screen bg-brand-light">
            <div class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-10">
                <div class="mb-8 flex justify-center">
                <a href="/">
                    <x-application-logo />
                </a>
                </div>

                <div class="mx-auto w-full max-w-md overflow-hidden rounded-3xl border border-white/80 bg-white p-8 shadow-soft">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
