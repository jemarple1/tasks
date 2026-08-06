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
    <meta name="theme-color" content="{{ $weather['theme'] ?? '#a8daf0' }}">
    <meta name="format-detection" content="telephone=no">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>@yield('title', 'Tend')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-dvh font-sans text-garden-text antialiased"
    style="background: linear-gradient(to bottom, {{ $weather['from'] ?? '#8ecae6' }}, {{ $weather['via'] ?? '#b8dff5' }}, {{ $weather['to'] ?? '#e0f2fe' }});"
>
    @auth
        <div id="tree-bed" class="tree-bed" aria-hidden="true">
            <span
                id="garden-tree"
                class="garden-tree"
                style="font-size: {{ $treeSize ?? '3vmin' }};"
            >{{ $treeEmoji ?? '🌳' }}</span>
        </div>
    @endauth

    <div class="main-content mx-auto flex min-h-dvh max-w-lg flex-col px-5 pb-safe-tree pt-safe @yield('content-class')">
        @yield('content')
    </div>

    @stack('modals')
</body>
</html>
