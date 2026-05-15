<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DailyCart') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-brand-light text-brand-text">
            @include('layouts.navigation')

            <div class="flex">
                <x-sidebar />

                <div class="min-w-0 flex-1">
                    @isset($header)
                        <header class="border-b border-green-100 bg-white/70">
                            <div class="dc-container py-6">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main class="min-h-[70vh]">
                        {{ $slot }}
                    </main>
                </div>
            </div>

            <x-footer />
        </div>
    </body>
</html>
