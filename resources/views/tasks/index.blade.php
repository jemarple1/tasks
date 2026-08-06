@extends('layouts.app')

@section('title', 'Tend')

@section('content')
    <header class="flex items-center justify-between pb-4 pt-2">
        <div class="flex items-center gap-2">
            <a href="{{ route('weather.index') }}" class="flex items-center gap-2 rounded-full border-2 border-white/60 bg-white/70 px-2.5 py-1 shadow-sm transition active:scale-95" aria-label="Weather forecast">
                <span class="text-4xl" role="img" aria-hidden="true">{{ $weather['emoji'] ?? '🌤' }}</span>
                @if(isset($weather['temperature']))
                    <span class="font-sans text-lg font-medium text-garden-text">{{ $weather['temperature'] }}°</span>
                @endif
            </a>
        </div>
        <div class="flex items-center gap-1.5">
            <a href="{{ route('calendar.index') }}" class="nav-btn" aria-label="Calendar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </a>
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

    <div class="relative mb-4 rounded-2xl border-2 border-white/70 bg-white/70 p-1 shadow-sm backdrop-blur-sm">
        <div data-tab-indicator class="tab-indicator absolute inset-y-1 left-1 w-[calc(50%-4px)] rounded-xl bg-white shadow-md"></div>
        <div class="relative grid grid-cols-2 gap-1">
            <button type="button" data-tab="immediate" class="relative z-10 rounded-xl py-2.5 font-sans text-base font-semibold text-garden-text transition">
                <span class="font-title font-semibold">Immediate</span>
                <span class="ml-1 text-sm text-garden-accent">({{ $immediateTasks->count() }})</span>
            </button>
            <button type="button" data-tab="longterm" class="relative z-10 rounded-xl py-2.5 font-sans text-base text-garden-muted transition">
                <span class="font-title font-semibold">Long-term</span>
                <span class="ml-1 text-sm">({{ $longtermTasks->count() }})</span>
            </button>
        </div>
    </div>

    <div data-panel="immediate" class="flex flex-1 flex-col gap-2">
        @forelse ($immediateTasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="py-12 text-center">
                <p class="font-title text-xl text-garden-text">Nothing urgent</p>
                <p class="mt-1 font-sans text-base text-garden-muted">Tap + to tend a task</p>
            </div>
        @endforelse
    </div>

    <div data-panel="longterm" class="hidden flex-1 flex-col gap-2">
        @forelse ($longtermTasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="py-12 text-center">
                <p class="font-title text-xl text-garden-text">No long-term goals</p>
            </div>
        @endforelse
    </div>

    <p class="mt-3 text-center font-sans text-xs text-garden-muted">
        Tap to edit · Swipe left complete · Swipe right +7d
    </p>

    <button id="fab-add" type="button" class="fab fixed z-40 flex h-14 w-14 items-center justify-center rounded-full border-2 border-white/50 bg-garden-accent text-2xl font-light text-white transition active:scale-[0.94]" aria-label="Add task">+</button>
@endsection

@push('modals')
    <div id="task-modal" class="modal-backdrop fixed inset-0 z-50 bg-garden-deep/50">
        <div class="modal-fullscreen fixed inset-0 flex flex-col bg-white pt-safe">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                <h2 id="task-modal-title" class="font-title text-2xl font-bold text-garden-text">New task</h2>
                <button id="modal-close" type="button" class="font-sans text-lg font-medium text-garden-accent">Cancel</button>
            </div>
            <form id="task-form" action="{{ route('tasks.store') }}" method="POST" class="flex flex-1 flex-col overflow-y-auto px-5 py-5">
                @csrf
                <input type="hidden" id="task-form-method" name="_method" value="" disabled>
                <div class="mb-4">
                    <label for="task-title" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Title</label>
                    <input id="task-title" type="text" name="title" required maxlength="255" autocomplete="off" class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent focus:ring-4 focus:ring-garden-accent/20">
                </div>
                <div class="mb-4">
                    <label for="task-notes" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Notes</label>
                    <textarea id="task-notes" name="notes" rows="3" maxlength="2000" class="input-field w-full resize-none rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent focus:ring-4 focus:ring-garden-accent/20"></textarea>
                </div>
                <div class="mb-4" id="assignee-field">
                    <label for="assignee_username" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Assign to</label>
                    <select id="assignee_username" name="assignee_username" class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
                        <option value="">Me ({{ '@'.auth()->user()->username }})</option>
                        @foreach (auth()->user()->connectedUsers() as $person)
                            <option value="{{ $person->username }}">{{ '@'.$person->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4" id="recurrence-field">
                    <p class="mb-2 font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Repeat</p>
                    <select id="task-recurrence" name="recurrence" class="input-field mb-2 w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
                        <option value="none">Does not repeat</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                    <input type="date" id="task-recurrence-until" name="recurrence_until" class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent" placeholder="Repeat until">
                </div>
                <div class="mb-6">
                    <p class="mb-2 font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Category</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer"><input type="radio" name="category" value="immediate" class="peer sr-only" checked><span class="flex items-center justify-center rounded-xl border-2 border-slate-200 bg-slate-50 py-3 font-title text-base peer-checked:border-garden-accent peer-checked:bg-blue-50 peer-checked:font-semibold peer-checked:text-garden-accent">Immediate</span></label>
                        <label class="cursor-pointer"><input type="radio" name="category" value="longterm" class="peer sr-only"><span class="flex items-center justify-center rounded-xl border-2 border-slate-200 bg-slate-50 py-3 font-title text-base peer-checked:border-garden-accent peer-checked:bg-blue-50 peer-checked:font-semibold peer-checked:text-garden-accent">Long-term</span></label>
                    </div>
                </div>
                <button id="task-form-submit" type="submit" class="mt-auto w-full rounded-xl bg-garden-accent py-3.5 font-sans text-lg font-semibold text-white shadow-lg">Save task</button>
            </form>
        </div>
    </div>
@endpush
