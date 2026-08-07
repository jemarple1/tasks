@extends('layouts.app')

@section('title', 'Notifications — Tend')

@section('content')
    <header class="page-header flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
                <x-icon name="chevron-left" />
            </a>
            <h1 class="page-title truncate">Notifications</h1>
        </div>
        @if ($notifications->whereNull('read_at')->isNotEmpty())
            <form action="{{ route('notifications.read-all') }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="font-sans text-[13px] font-semibold text-garden-accent">Mark all read</button>
            </form>
        @endif
    </header>

    <ul class="space-y-2">
        @forelse ($notifications as $notification)
            <li class="surface-card px-4 py-3 {{ $notification->read_at ? 'opacity-70' : '' }}" @if(!$notification->read_at) style="border-left: 4px solid var(--color-garden-accent)" @endif>
                <p class="font-sans text-[15px] leading-snug text-garden-text">{{ $notification->data['message'] ?? 'Notification' }}</p>
                <div class="mt-1.5 flex items-center justify-between gap-3">
                    <p class="font-sans text-xs text-garden-muted">{{ $notification->created_at->diffForHumans() }}</p>
                    @unless ($notification->read_at)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="font-sans text-xs font-semibold text-garden-accent">Mark read</button>
                        </form>
                    @endunless
                </div>
            </li>
        @empty
            <li class="py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white/70 text-garden-muted">
                    <x-icon name="bell" class="h-7 w-7" />
                </div>
                <p class="empty-title">All caught up</p>
                <p class="empty-body">You have no notifications</p>
            </li>
        @endforelse
    </ul>
@endsection
