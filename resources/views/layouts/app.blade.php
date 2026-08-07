<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Tend">
    <meta name="application-name" content="Tend">
    <meta name="theme-color" content="#79c2ec">
    <meta name="format-detection" content="telephone=no">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>@yield('title', 'Tend')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sky-background min-h-dvh font-sans text-garden-text antialiased @auth has-bottom-nav @endauth">
    <div class="main-content mx-auto flex min-h-dvh max-w-lg flex-col overflow-x-hidden px-4 pb-safe-nav pt-safe @yield('content-class')">
        @yield('content')
    </div>

    @auth
        @include('partials.bottom-nav')
        @include('tasks.partials.modal')
    @endauth

    @stack('modals')
</body>
</html>
