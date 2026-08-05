@extends('layouts.app')

@section('title', 'Complete — Task Garden')

@section('content')
    <header class="flex items-center gap-3 pb-6 pt-2">
        <a
            href="{{ route('tasks.index') }}"
            class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-white/60 bg-white/80 text-garden-text shadow-sm transition active:scale-95"
            aria-label="Back to tasks"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="font-serif text-2xl font-bold italic text-garden-text">Complete</h1>
            <p class="font-sans text-base text-garden-muted">{{ $completedTasks->count() }} finished</p>
        </div>
    </header>

    <div class="flex flex-col gap-3.5">
        @forelse ($completedTasks as $task)
            <div class="rounded-2xl border-2 border-slate-200 bg-white/90 px-5 py-4 shadow-sm">
                <p class="font-serif text-lg text-garden-muted line-through decoration-garden-muted/50">{{ $task->title }}</p>
                <div class="mt-2 flex items-center gap-2 font-sans text-base text-garden-muted">
                    <span class="rounded-full bg-slate-100 px-3 py-0.5 font-medium capitalize">{{ $task->category === 'longterm' ? 'Long-term' : 'Immediate' }}</span>
                    <span>{{ $task->archived_at->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <span class="mb-4 text-5xl opacity-50">🌱</span>
                <p class="font-serif text-xl italic text-garden-text">Nothing completed yet</p>
                <p class="mt-2 font-sans text-base text-garden-muted">Swipe left on a task to finish it</p>
            </div>
        @endforelse
    </div>
@endsection
