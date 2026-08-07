@extends('layouts.app')

@section('title', 'Tend')

@section('content')
    <header class="page-header flex items-center justify-between pb-2">
        @include('partials.weather-badge')
        <div class="flex items-center gap-1.5">
            <a href="{{ route('notifications.index') }}" class="nav-btn relative" aria-label="Notifications">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
                @if(($unreadNotifications ?? 0) > 0)
                    <span class="nav-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                @endif
            </a>
            <a href="{{ route('tasks.complete.index') }}" class="nav-btn" aria-label="Completed">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </a>
            <a href="{{ route('settings.index') }}" class="nav-btn" aria-label="Settings">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
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
