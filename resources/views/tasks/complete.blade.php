@extends('layouts.app')

@section('title', 'Completed — Tend')

@section('content')
    <header class="page-header flex items-center gap-3">
        <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
            <x-icon name="chevron-left" />
        </a>
        <div>
            <h1 class="page-title">Completed</h1>
            <p class="page-subtitle">{{ $completedTasks->count() }} finished</p>
        </div>
    </header>

    <div class="flex flex-col gap-2">
        @forelse ($completedTasks as $task)
            <div class="surface-card flex items-center gap-3 px-4 py-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <x-icon name="check" class="h-3.5 w-3.5" :stroke="3" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-sans text-[15px] font-medium text-garden-muted line-through">{{ $task->title }}</p>
                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2 font-sans text-xs text-garden-muted">
                        @if ($task->taskCategory)
                            <span class="font-semibold" style="color: {{ $task->taskCategory->color }}">{{ $task->taskCategory->name }}</span>
                        @endif
                        @if ($task->isFromOtherUser())
                            <span>from {{ '@'.$task->creator->username }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white/70 text-garden-muted">
                    <x-icon name="check-circle" class="h-7 w-7" />
                </div>
                <p class="empty-title">Nothing completed yet</p>
                <p class="empty-body">Finished tasks will collect here</p>
            </div>
        @endforelse
    </div>
@endsection
