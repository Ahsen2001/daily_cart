<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Stores') }} - DailyCart</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-light font-sans text-brand-text">
    <header class="sticky top-0 z-40 border-b border-brand-border bg-white/95 backdrop-blur-xl"><div class="dc-container flex h-20 items-center justify-between"><a href="{{ route('home') }}"><x-application-logo /></a><a class="dc-button-secondary" href="{{ route('products.index') }}">{{ __('Products') }}</a></div></header>
    <main class="py-10 sm:py-14"><div class="dc-container">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"><div><p class="font-semibold text-brand-dark">{{ __('DailyCart Marketplace') }}</p><h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ __('Browse stores') }}</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-brand-text/65">{{ __('Discover approved vendor storefronts while continuing to shop products across every vendor.') }}</p></div>
            <form method="GET" class="grid gap-3 sm:grid-cols-3 lg:min-w-[650px]"><x-search-bar name="search" placeholder="Search stores..." :value="request('search')" /><select name="category" class="rounded-2xl border-brand-border"><option value="">{{ __('All categories') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select><button class="dc-button" type="submit">{{ __('Search') }}</button></form>
        </div>
        <div class="mt-8 flex items-center justify-between"><p class="text-sm font-semibold text-brand-text/60">{{ __('Approved storefronts') }}</p><a class="text-sm font-bold text-brand-dark hover:underline" href="{{ route('stores.index', ['featured' => 1]) }}">{{ __('Featured stores') }}</a></div>
        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@forelse($stores as $store)<x-store-card :store="$store" />@empty<div class="dc-card sm:col-span-2 lg:col-span-3"><p class="text-sm text-brand-text/65">{{ __('No stores match those filters yet.') }}</p></div>@endforelse</div>
        <div class="mt-8">{{ $stores->links() }}</div>
    </div></main><x-footer />
</body></html>
