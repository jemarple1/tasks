@extends('layouts.app')

@section('title', 'Archive — Sky Ledger')

@section('content')
    <header class="flex items-center gap-3 pb-6 pt-4">
        <a
            href="{{ route('tasks.index') }}"
            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/60 text-sky-deep shadow-sm backdrop-blur-sm transition active:scale-95"
            aria-label="Back to tasks"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Archive</h1>
            <p class="text-sm text-sky-muted">{{ $archivedTasks->count() }} completed</p>
        </div>
    </header>

    <div class="flex flex-col gap-3">
        @forelse ($archivedTasks as $task)
            <div class="rounded-2xl border border-blue-100/60 bg-sky-task/80 px-4 py-3.5 shadow-sm">
                <p class="text-[15px] leading-snug text-sky-muted line-through decoration-sky-muted/40">{{ $task->title }}</p>
                <div class="mt-1.5 flex items-center gap-2 text-xs text-sky-muted/80">
                    <span class="rounded-full bg-white/70 px-2 py-0.5 capitalize">{{ $task->category === 'longterm' ? 'Long-term' : 'Immediate' }}</span>
                    <span>{{ $task->archived_at->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <span class="mb-3 text-4xl opacity-50">📦</span>
                <p class="text-sm text-sky-deep/80">Archive is empty</p>
                <p class="mt-1 text-xs text-sky-muted">Swipe tasks left to send them here</p>
            </div>
        @endforelse
    </div>
@endsection
