@extends('layouts.app')

@section('title', 'Sky Ledger')

@section('content')
    <header class="flex items-center justify-between pb-6 pt-4">
        <span class="text-3xl" role="img" aria-label="Sun behind cloud">🌤</span>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('tasks.archive.index') }}"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-white/40 text-sky-deep shadow-sm backdrop-blur-sm transition active:scale-95"
                aria-label="View archive"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/40 text-sky-deep shadow-sm backdrop-blur-sm transition active:scale-95"
                    aria-label="Sign out"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <div class="relative mb-5 rounded-2xl bg-white/60 p-1 shadow-sm backdrop-blur-sm">
        <div
            data-tab-indicator
            class="tab-indicator absolute inset-y-1 left-1 w-[calc(50%-4px)] rounded-xl bg-white shadow-sm"
        ></div>
        <div class="relative grid grid-cols-2 gap-1">
            <button
                type="button"
                data-tab="immediate"
                class="relative z-10 rounded-xl py-2.5 text-sm font-medium text-sky-deep transition"
            >
                Immediate
                <span class="ml-1 text-xs text-sky-accent">{{ $immediateTasks->count() }}</span>
            </button>
            <button
                type="button"
                data-tab="longterm"
                class="relative z-10 rounded-xl py-2.5 text-sm font-medium text-sky-muted transition"
            >
                Long-term
                <span class="ml-1 text-xs opacity-70">{{ $longtermTasks->count() }}</span>
            </button>
        </div>
    </div>

    <div data-panel="immediate" class="flex flex-1 flex-col gap-3">
        @forelse ($immediateTasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="flex flex-1 flex-col items-center justify-center py-16 text-center">
                <span class="mb-3 text-4xl opacity-50">🌤</span>
                <p class="text-sm text-sky-deep/80">Nothing urgent right now</p>
                <p class="mt-1 text-xs text-sky-muted">Tap + to add a task</p>
            </div>
        @endforelse
    </div>

    <div data-panel="longterm" class="hidden flex-1 flex-col gap-3">
        @forelse ($longtermTasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="flex flex-1 flex-col items-center justify-center py-16 text-center">
                <span class="mb-3 text-4xl opacity-50">☁️</span>
                <p class="text-sm text-sky-deep/80">No long-term goals yet</p>
                <p class="mt-1 text-xs text-sky-muted">Dream big — add one below</p>
            </div>
        @endforelse
    </div>

    <p class="mt-6 text-center text-xs text-sky-deep/50">Swipe left on a task to archive</p>

    <button
        id="fab-add"
        type="button"
        class="fab fixed z-40 flex h-14 w-14 items-center justify-center rounded-full bg-sky-accent text-2xl font-light text-white shadow-lg transition active:scale-[0.94]"
        aria-label="Add task"
    >
        +
    </button>
@endsection

@push('modals')
    <div id="add-modal" class="modal-backdrop fixed inset-0 z-50 flex items-end justify-center bg-sky-deep/40 backdrop-blur-[2px]">
        <div class="modal-sheet w-full max-w-lg rounded-t-3xl bg-white px-6 pb-safe pt-4 shadow-2xl">
            <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-gray-200"></div>
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-sky-deep">New task</h2>
                <button id="modal-close" type="button" class="text-sm text-sky-muted">Cancel</button>
            </div>

            <form id="add-task-form" action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <input
                        type="text"
                        name="title"
                        placeholder="What needs doing?"
                        required
                        maxlength="255"
                        class="w-full rounded-xl border border-blue-100 bg-blue-50/50 px-4 py-3.5 text-[15px] text-sky-deep outline-none ring-sky-accent/30 placeholder:text-sky-muted/60 focus:border-sky-accent/40 focus:ring-2"
                    >
                </div>

                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-sky-muted">Category</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="immediate" class="peer sr-only" checked>
                            <span class="flex items-center justify-center rounded-xl border-2 border-transparent bg-blue-50/50 py-3 text-sm font-medium text-sky-muted transition peer-checked:border-sky-card peer-checked:bg-blue-100 peer-checked:text-sky-card">
                                Immediate
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="longterm" class="peer sr-only">
                            <span class="flex items-center justify-center rounded-xl border-2 border-transparent bg-blue-50/50 py-3 text-sm font-medium text-sky-muted transition peer-checked:border-sky-card peer-checked:bg-blue-100 peer-checked:text-sky-card">
                                Long-term
                            </span>
                        </label>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-sky-accent py-3.5 text-sm font-semibold text-white shadow-md transition active:scale-[0.98]"
                >
                    Add task
                </button>
            </form>
        </div>
    </div>
@endpush
