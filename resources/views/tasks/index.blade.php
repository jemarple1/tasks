@extends('layouts.app')

@section('title', 'Tend')

@section('content')
    <header class="page-header flex items-center justify-between gap-2">
        @include('partials.weather-badge')
        <div class="flex items-center gap-1.5">
            <a href="{{ route('notifications.index') }}" class="nav-btn" aria-label="Notifications">
                <x-icon name="bell" />
                @if(($unreadNotifications ?? 0) > 0)
                    <span class="nav-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                @endif
            </a>
            <a href="{{ route('tasks.complete.index') }}" class="nav-btn" aria-label="Completed tasks">
                <x-icon name="check-circle" />
            </a>
            <a href="{{ route('settings.index') }}" class="nav-btn" aria-label="Settings">
                <x-icon name="sliders" />
            </a>
        </div>
    </header>

    <div class="mb-4">
        <h1 class="page-title">Tasks</h1>
        <p class="page-subtitle">{{ $tasks->count() }} {{ Str::plural('task', $tasks->count()) }} on your plate</p>
    </div>

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

    <div class="task-list flex flex-1 flex-col gap-2.5">
        @forelse ($tasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white/70 text-garden-muted">
                    <x-icon name="sparkle" class="h-7 w-7" />
                </div>
                <p class="empty-title">All clear</p>
                <p class="empty-body">Tap the + button to add your first task</p>
            </div>
        @endforelse
    </div>

    @if ($tasks->isNotEmpty())
        <p class="hint-text mt-5 text-center">
            Tap the circle to complete · swipe left to remind tomorrow · swipe right for +7 days
        </p>
    @endif
@endsection
