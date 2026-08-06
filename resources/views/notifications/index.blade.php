@extends('layouts.app')

@section('title', 'Notifications — Tend')

@section('content')
    <header class="flex items-center justify-between pb-4 pt-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="font-title text-2xl font-bold text-garden-text">Notifications</h1>
        </div>
        @if ($notifications->whereNull('read_at')->isNotEmpty())
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="font-sans text-sm font-medium text-garden-accent">Mark all read</button>
            </form>
        @endif
    </header>

    <ul class="space-y-2">
        @forelse ($notifications as $notification)
            <li class="rounded-xl border-2 px-4 py-3 {{ $notification->read_at ? 'border-white/60 bg-white/60' : 'border-garden-accent/30 bg-white/90' }}">
                <p class="font-sans text-base">{{ $notification->data['message'] ?? 'Notification' }}</p>
                <p class="mt-1 font-sans text-sm text-garden-muted">{{ $notification->created_at->diffForHumans() }}</p>
                @unless ($notification->read_at)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="mt-2">
                        @csrf @method('PATCH')
                        <button type="submit" class="font-sans text-sm text-garden-accent">Mark read</button>
                    </form>
                @endunless
            </li>
        @empty
            <li class="py-12 text-center font-sans text-garden-muted">No notifications yet</li>
        @endforelse
    </ul>
@endsection
