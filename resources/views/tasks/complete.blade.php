@extends('layouts.app')

@section('title', 'Complete — Tend')

@section('content')
    <header class="flex items-center gap-3 pb-4 pt-2">
        <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="font-title text-2xl font-bold text-garden-text">Complete</h1>
            <p class="font-sans text-base text-garden-muted">{{ $completedTasks->count() }} finished</p>
        </div>
    </header>

    <div class="flex flex-col gap-2">
        @forelse ($completedTasks as $task)
            <div class="rounded-xl border-2 border-slate-200 bg-white/90 px-4 py-2.5 shadow-sm">
                <p class="font-title text-base text-garden-muted line-through">{{ $task->title }}</p>
                <div class="mt-1 flex flex-wrap items-center gap-2 font-sans text-sm text-garden-muted">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 capitalize">{{ $task->category === 'longterm' ? 'Long-term' : 'Immediate' }}</span>
                    @if ($task->isFromOtherUser())
                        <span class="text-amber-700">from {{ '@'.$task->creator->username }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <p class="font-title text-xl text-garden-text">Nothing completed yet</p>
            </div>
        @endforelse
    </div>
@endsection
