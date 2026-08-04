<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Sky Ledger">
    <meta name="theme-color" content="#e8f4fc">
    <meta name="format-detection" content="telephone=no">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>@yield('title', 'Sky Ledger')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-linear-to-b from-sky-pale via-sky-light to-sky-mid font-sans text-sky-deep antialiased">
    <div class="mx-auto flex min-h-dvh max-w-lg flex-col px-4 pb-safe-fab pt-safe">
        @yield('content')
    </div>

    @stack('modals')
</body>
</html>
