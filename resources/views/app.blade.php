<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $branding['appName'] }} — Privacy-first web analytics. No cookies, no IP storage, just clean insights.">
        <meta name="theme-color" content="{{ $branding['primaryColor'] }}">

        <link rel="icon" type="image/png" href="{{ $branding['faviconUrl'] }}">
        <link rel="apple-touch-icon" href="{{ $branding['faviconUrl'] }}">

        <meta property="og:title" content="{{ $branding['appName'] }} — Privacy-First Analytics">
        <meta property="og:description" content="Lightweight, cookie-free analytics for modern websites.">
        <meta property="og:image" content="{{ $branding['siteLogoUrl'] }}">
        <meta property="og:type" content="website">

        <title inertia>{{ $branding['appName'] }}</title>

        <style>:root { --brand-primary: {{ $branding['primaryColor'] }}; }</style>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
