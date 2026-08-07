@extends('layouts.app')

@section('title', 'Tend')

@section('content')
    <header class="page-header flex items-center justify-between pb-2">
        <a href="{{ route('weather.index') }}" class="surface-card flex items-center gap-2 px-3 py-2 transition active:scale-95" aria-label="Weather">
            <span class="text-2xl" role="img" aria-hidden="true">{{ $weather['emoji'] ?? '🌤' }}</span>
            @if(isset($weather['temperature']))
                <span class="font-sans text-sm font-semibold text-garden-text">{{ $weather['temperature'] }}°</span>
            @endif
        </a>
        <div class="flex items-center gap-1.5">
            <a href="{{ route('notifications.index') }}" class="nav-btn relative" aria-label="Notifications">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if(($unreadNotifications ?? 0) > 0)
                    <span class="nav-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                @endif
            </a>
            <a href="{{ route('tasks.complete.index') }}" class="nav-btn" aria-label="Completed">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
            </a>
            <a href="{{ route('settings.index') }}" class="nav-btn" aria-label="Settings">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            </a>
        </div>
    </header>

    <div class="category-filter mb-4">
        <a href="{{ route('tasks.index') }}" class="category-pill {{ !$activeCategory ? 'active' : '' }}">All</a>
        @foreach ($categories as $category)
            <a
                href="{{ route('tasks.index', ['category' => $category->id]) }}"
                class="category-pill {{ (string) $activeCategory === (string) $category->id ? 'active' : '' }}"
            >
                <span class="category-dot" style="background: {{ $category->color }}"></span>
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    <div class="task-list flex flex-1 flex-col gap-3">
        @forelse ($tasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="py-16 text-center">
                <p class="font-title text-2xl font-bold text-garden-text">Nothing here yet</p>
                <p class="mt-2 font-sans text-base text-garden-muted">Tap + below to add a task</p>
            </div>
        @endforelse
    </div>

    <p class="mt-4 text-center font-sans text-xs text-garden-muted">
        Tap circle to complete · Tap task to edit · Swipe left remind tomorrow · Swipe right +7 days
    </p>
@endsection
