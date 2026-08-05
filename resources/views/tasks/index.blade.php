@extends('layouts.app')

@section('title', 'Task Garden')

@section('content')
    <header class="flex items-center justify-between pb-5 pt-2">
        <div class="flex items-center gap-2">
            <span class="text-4xl" role="img" aria-label="{{ $weather['label'] ?? 'Weather' }}">{{ $weather['emoji'] ?? '🌤' }}</span>
            @if(isset($weather['temperature']))
                <span class="font-sans text-lg font-medium text-garden-text">{{ $weather['temperature'] }}°</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('tasks.complete.index') }}"
                class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-white/60 bg-white/80 text-garden-text shadow-sm transition active:scale-95"
                aria-label="View completed tasks"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-white/60 bg-white/80 text-garden-text shadow-sm transition active:scale-95"
                    aria-label="Sign out"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <div class="relative mb-6 rounded-2xl border-2 border-white/70 bg-white/70 p-1.5 shadow-sm backdrop-blur-sm">
        <div
            data-tab-indicator
            class="tab-indicator absolute inset-y-1.5 left-1.5 w-[calc(50%-6px)] rounded-xl bg-white shadow-md"
        ></div>
        <div class="relative grid grid-cols-2 gap-1">
            <button
                type="button"
                data-tab="immediate"
                class="relative z-10 rounded-xl py-3 font-sans text-base font-semibold text-garden-text transition"
            >
                <em class="font-serif not-italic">Immediate</em>
                <span class="ml-1 text-sm text-garden-accent">({{ $immediateTasks->count() }})</span>
            </button>
            <button
                type="button"
                data-tab="longterm"
                class="relative z-10 rounded-xl py-3 font-sans text-base text-garden-muted transition"
            >
                <em class="font-serif not-italic">Long-term</em>
                <span class="ml-1 text-sm">({{ $longtermTasks->count() }})</span>
            </button>
        </div>
    </div>

    <div data-panel="immediate" class="flex flex-1 flex-col gap-3.5">
        @forelse ($immediateTasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="flex flex-1 flex-col items-center justify-center py-16 text-center">
                <span class="mb-4 text-5xl opacity-60">{{ $weather['emoji'] ?? '🌤' }}</span>
                <p class="font-serif text-xl italic text-garden-text">Nothing urgent right now</p>
                <p class="mt-2 font-sans text-base text-garden-muted">Tap + to plant a task</p>
            </div>
        @endforelse
    </div>

    <div data-panel="longterm" class="hidden flex-1 flex-col gap-3.5">
        @forelse ($longtermTasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="flex flex-1 flex-col items-center justify-center py-16 text-center">
                <span class="mb-4 text-5xl opacity-60">☁️</span>
                <p class="font-serif text-xl italic text-garden-text">No long-term goals yet</p>
                <p class="mt-2 font-sans text-base text-garden-muted">Dream big — add one above</p>
            </div>
        @endforelse
    </div>

    <p class="mt-6 text-center font-sans text-sm text-garden-muted">
        Swipe left to <strong class="font-semibold text-garden-text">complete</strong> · Swipe right for <strong class="font-semibold text-garden-text">+7 days</strong>
    </p>

    <button
        id="fab-add"
        type="button"
        class="fab fixed z-40 flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/50 bg-garden-accent text-3xl font-light text-white transition active:scale-[0.94]"
        aria-label="Add task"
    >
        +
    </button>
@endsection

@push('modals')
    <div id="add-modal" class="modal-backdrop fixed inset-0 z-50 bg-garden-deep/50">
        <div class="modal-fullscreen fixed inset-0 flex flex-col bg-white pt-safe">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-serif text-2xl font-bold italic text-garden-text">New task</h2>
                <button id="modal-close" type="button" class="font-sans text-lg font-medium text-garden-accent">Cancel</button>
            </div>

            <form id="add-task-form" action="{{ route('tasks.store') }}" method="POST" class="flex flex-1 flex-col px-5 py-6">
                @csrf
                <div class="mb-6">
                    <label for="task-title" class="mb-2 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">What needs doing?</label>
                    <input
                        id="task-title"
                        type="text"
                        name="title"
                        placeholder="Write your task…"
                        required
                        maxlength="255"
                        autocomplete="off"
                        class="input-field w-full rounded-2xl border-2 border-slate-300 bg-slate-50 px-5 py-4 text-garden-text outline-none placeholder:text-garden-muted/60 focus:border-garden-accent focus:ring-4 focus:ring-garden-accent/20"
                    >
                </div>

                <div class="mb-8">
                    <p class="mb-3 font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Category</p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="immediate" class="peer sr-only" checked>
                            <span class="flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-slate-50 py-4 font-serif text-lg text-garden-muted transition peer-checked:border-garden-accent peer-checked:bg-blue-50 peer-checked:font-semibold peer-checked:text-garden-accent">
                                Immediate
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="longterm" class="peer sr-only">
                            <span class="flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-slate-50 py-4 font-serif text-lg text-garden-muted transition peer-checked:border-garden-accent peer-checked:bg-blue-50 peer-checked:font-semibold peer-checked:text-garden-accent">
                                Long-term
                            </span>
                        </label>
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-auto w-full rounded-2xl bg-garden-accent py-4 font-sans text-lg font-semibold text-white shadow-lg transition active:scale-[0.98]"
                >
                    Plant task
                </button>
            </form>
        </div>
    </div>
@endpush
