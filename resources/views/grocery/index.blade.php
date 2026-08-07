@extends('layouts.app')

@section('title', 'Grocery — Tend')

@section('content')
    <header class="page-header flex items-center justify-between gap-2">
        @include('partials.weather-badge')
        <a href="{{ route('settings.index') }}" class="nav-btn" aria-label="Settings">
            <x-icon name="sliders" />
        </a>
    </header>

    <div class="mb-4">
        <h1 class="page-title">Grocery</h1>
        <p class="page-subtitle">
            @if ($connections->isNotEmpty())
                One shared list · {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
            @else
                Add someone in Settings to share this list
            @endif
        </p>
    </div>

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
        <input type="text" name="name" placeholder="Add an item…" required maxlength="120" class="input-field">
        <div class="flex gap-2">
            <select name="recurrence" class="input-field min-w-0 flex-1">
                <option value="none">One-time</option>
                <option value="weekly">Weekly</option>
                <option value="biweekly">Every 2 weeks</option>
                <option value="monthly">Monthly</option>
            </select>
            <button type="submit" class="btn-primary shrink-0 px-5">Add</button>
        </div>
    </form>

    <div class="grocery-list space-y-2">
        @forelse ($items as $item)
            @php $c = $userColors[$item->user_id] ?? $userColors[auth()->id()]; @endphp
            <div class="grocery-item surface-card" data-grocery-item data-complete-url="{{ route('grocery.complete', $item) }}" style="border-left: 4px solid {{ $c['border'] }}">
                <button type="button" class="grocery-check" aria-label="Mark purchased">
                    <span class="grocery-check-ring"><x-icon name="check" :stroke="3" /></span>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="font-sans text-[15px] font-semibold tracking-tight text-garden-text">{{ $item->name }}</p>
                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        @if (!$c['is_self'])
                            <span class="owner-badge" style="color: {{ $c['text'] }}">{{ '@'.$c['username'] }}</span>
                        @endif
                        @if ($item->isRecurring())
                            <span class="inline-flex items-center gap-1 font-sans text-xs text-garden-muted">
                                <x-icon name="repeat" class="h-3 w-3" />
                                {{ str_replace('biweekly', 'every 2 weeks', $item->recurrence) }}
                            </span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('grocery.destroy', $item) }}" method="POST" class="shrink-0">
                    @csrf @method('DELETE')
                    <button type="submit" class="nav-btn h-8 w-8 text-garden-muted" aria-label="Remove item">
                        <x-icon name="trash" class="h-4 w-4" />
                    </button>
                </form>
            </div>
        @empty
            <div class="py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white/70 text-garden-muted">
                    <x-icon name="cart" class="h-7 w-7" />
                </div>
                <p class="empty-title">List is empty</p>
                <p class="empty-body">Add the staples you buy regularly</p>
            </div>
        @endforelse
    </div>

    @if ($items->isNotEmpty())
        <p class="hint-text mt-5 text-center">Unchecked items clear automatically after one week</p>
    @endif
@endsection
