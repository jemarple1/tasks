@extends('layouts.app')

@section('title', 'Grocery — Tend')

@section('content')
    <header class="page-header flex items-center justify-between gap-2 pb-2">
        @include('partials.weather-badge')
        <div class="min-w-0 text-right">
            <h1 class="font-title text-2xl font-bold text-garden-text sm:text-3xl">Grocery</h1>
            <p class="mt-0.5 font-sans text-xs text-garden-muted sm:text-sm">
                @if ($connections->isNotEmpty())
                    Shared with your connections
                @else
                    Add someone in Settings to share
                @endif
            </p>
        </div>
    </header>

    @if ($connections->isNotEmpty())
        <div class="user-legend mb-3">
            @foreach ($userColors as $color)
                <span class="user-legend-item">
                    <span class="user-legend-dot" style="background: {{ $color['border'] }}"></span>
                    {{ $color['is_self'] ? 'You' : '@'.$color['username'] }}
                </span>
            @endforeach
        </div>
    @endif

    <form action="{{ route('grocery.store') }}" method="POST" class="surface-card form-stack mb-5 p-4">
        @csrf
        <input type="text" name="name" placeholder="Add item…" required maxlength="120" class="input-field">
        <div class="flex gap-2">
            <select name="recurrence" class="input-field min-w-0 flex-1 text-sm">
                <option value="none">One-time</option>
                <option value="weekly">Weekly</option>
                <option value="biweekly">Every 2 weeks</option>
                <option value="monthly">Monthly</option>
            </select>
            <button type="submit" class="btn-primary shrink-0 px-5 py-2.5 text-sm">Add</button>
        </div>
    </form>

    <div class="grocery-list space-y-2">
        @forelse ($items as $item)
            @php $c = $userColors[$item->user_id] ?? $userColors[auth()->id()]; @endphp
            <div class="grocery-item surface-card" data-grocery-item data-complete-url="{{ route('grocery.complete', $item) }}" style="border-left: 4px solid {{ $c['border'] }}">
                <button type="button" class="grocery-check" aria-label="Mark purchased">
                    <span class="grocery-check-ring"></span>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="font-sans text-base font-semibold text-garden-text">{{ $item->name }}</p>
                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        @if (!$c['is_self'])
                            <span class="owner-badge text-xs" style="color: {{ $c['text'] }}">{{ '@'.$c['username'] }}</span>
                        @endif
                        @if ($item->isRecurring())
                            <span class="font-sans text-xs text-garden-muted">↻ {{ str_replace('biweekly', 'every 2 weeks', $item->recurrence) }}</span>
                        @endif
                    </div>
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

    <p class="mt-4 text-center font-sans text-xs text-garden-muted">Unchecked items clear after one week</p>
@endsection
