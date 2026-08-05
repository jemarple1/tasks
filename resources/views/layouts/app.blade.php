<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Task Garden">
    <meta name="theme-color" content="{{ $weather['theme'] ?? '#a8daf0' }}">
    <meta name="format-detection" content="telephone=no">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>@yield('title', 'Task Garden')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-dvh font-sans text-garden-text antialiased"
    style="background: linear-gradient(to bottom, {{ $weather['from'] ?? '#8ecae6' }}, {{ $weather['via'] ?? '#b8dff5' }}, {{ $weather['to'] ?? '#e0f2fe' }});"
>
    <div class="mx-auto flex min-h-dvh max-w-lg flex-col px-5 pb-safe-garden pt-safe">
        @yield('content')
    </div>

    <div id="garden-bed" class="garden-bed" aria-hidden="true">
        @isset($flowers)
            @foreach ($flowers as $flower)
                <span
                    class="garden-flower"
                    style="left: {{ $flower->position_x }}%;"
                    data-flower-id="{{ $flower->id }}"
                >{{ $flower->emoji }}</span>
            @endforeach
        @endisset
    </div>

    @stack('modals')
</body>
</html>
