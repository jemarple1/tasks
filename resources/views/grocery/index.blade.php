@extends('layouts.app')

@section('title', 'Grocery — Tend')

@section('content')
    <header class="page-header flex items-end justify-between">
        <div>
            <h1 class="font-title text-3xl font-bold text-garden-text">Grocery</h1>
            <p class="mt-1 font-sans text-sm text-garden-muted">Items clear after one week</p>
        </div>
    </header>

    <form action="{{ route('grocery.store') }}" method="POST" class="surface-card mb-5 space-y-3 p-4">
        @csrf
        <input type="text" name="name" placeholder="Add item…" required maxlength="120" class="input-field w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-garden-accent">
        <div class="flex gap-2">
            <select name="recurrence" class="input-field min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-garden-accent">
                <option value="none">One-time</option>
                <option value="weekly">Weekly</option>
                <option value="biweekly">Every 2 weeks</option>
                <option value="monthly">Monthly</option>
            </select>
            <button type="submit" class="shrink-0 rounded-xl bg-garden-accent px-5 py-2.5 font-sans text-sm font-semibold text-white">Add</button>
        </div>
    </form>

    <div class="grocery-list space-y-2">
        @forelse ($items as $item)
            <div class="grocery-item surface-card" data-grocery-item data-complete-url="{{ route('grocery.complete', $item) }}">
                <button type="button" class="grocery-check" aria-label="Mark purchased">
                    <span class="grocery-check-ring"></span>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="font-sans text-base font-semibold text-garden-text">{{ $item->name }}</p>
                    @if ($item->isRecurring())
                        <p class="font-sans text-xs text-garden-muted">↻ {{ str_replace('biweekly', 'every 2 weeks', $item->recurrence) }}</p>
                    @endif
                </div>
                <form action="{{ route('grocery.destroy', $item) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="font-sans text-xs font-medium text-garden-muted">Remove</button>
                </form>
            </div>
        @empty
            <div class="py-16 text-center">
                <p class="font-title text-2xl font-bold text-garden-text">List is empty</p>
                <p class="mt-2 font-sans text-sm text-garden-muted">Add staples you buy regularly</p>
            </div>
        @endforelse
    </div>
@endsection
