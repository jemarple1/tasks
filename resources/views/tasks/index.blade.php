@extends('layouts.app')

@section('title', 'Tend')

@section('content')
    <header class="flex items-center justify-between pb-3 pt-2">
        <div class="flex items-center gap-2">
            <a href="{{ route('weather.index') }}" class="flex items-center gap-2 rounded-full border-2 border-white/60 bg-white/70 px-2.5 py-1 shadow-sm transition active:scale-95" aria-label="Weather forecast">
                <span class="text-3xl" role="img" aria-hidden="true">{{ $weather['emoji'] ?? '🌤' }}</span>
                @if(isset($weather['temperature']))
                    <span class="font-sans text-base font-medium text-garden-text">{{ $weather['temperature'] }}°</span>
                @endif
            </a>
        </div>
        <div class="flex items-center gap-1.5">
            <a href="{{ route('notifications.index') }}" class="nav-btn relative" aria-label="Notifications">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if(($unreadNotifications ?? 0) > 0)
                    <span class="nav-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                @endif
            </a>
            <a href="{{ route('tasks.complete.index') }}" class="nav-btn" aria-label="Completed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </a>
            <a href="{{ route('settings.index') }}" class="nav-btn" aria-label="Settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
