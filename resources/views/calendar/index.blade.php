@extends('layouts.app')

@section('title', 'Calendar — Tend')
@section('content-class', 'calendar-layout')

@section('content')
@php
    $params = fn ($d, $v = null) => route('calendar.index', $calendarParams($d, $v ?? $view));
    $prevDate = match($view) {
        'week' => $date->copy()->subWeek(),
        'day' => $date->copy()->subDay(),
        default => $date->copy()->subMonth(),
    };
    $nextDate = match($view) {
        'week' => $date->copy()->addWeek(),
        'day' => $date->copy()->addDay(),
        default => $date->copy()->addMonth(),
    };
    $eventColor = fn ($userId) => $userColors[$userId] ?? $userColors[auth()->id()];
@endphp

<div class="calendar-page">
    <header class="page-header mb-3 flex shrink-0 items-center justify-between gap-2">
        @include('partials.weather-badge')
        <div class="flex min-w-0 flex-1 items-center justify-end gap-1">
            <h1 class="truncate font-title text-xl font-bold text-garden-text sm:text-2xl">{{ $label }}</h1>
            <a href="{{ $params($prevDate) }}" class="nav-btn shrink-0" aria-label="Previous">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <a href="{{ $params($nextDate) }}" class="nav-btn shrink-0" aria-label="Next">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
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

    <button type="button" id="calendar-add-toggle" class="add-panel-toggle mb-3">
        <span class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            Add event
        </span>
        <svg viewBox="0 0 24 24" class="h-4 w-4 opacity-60" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 9l6 6 6-6"/></svg>
    </button>

    <div id="calendar-add-panel" class="surface-card form-stack mb-4 hidden p-4">
        <form action="{{ route('calendar.store') }}" method="POST" class="space-y-3">
            @csrf
            <input type="text" name="title" placeholder="Event title" required class="input-field">
            <input type="datetime-local" name="starts_at" required value="{{ $date->format('Y-m-d\TH:i') }}" class="input-field">
            <input type="datetime-local" name="ends_at" placeholder="End time" class="input-field">
            <select name="recurrence" class="input-field">
                <option value="none">Does not repeat</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
            <input type="date" name="recurrence_until" class="input-field">
            @if ($connections->isNotEmpty())
                <div>
                    <p class="field-label">Tag people</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($connections as $person)
                            <label class="checkbox-chip">
                                <input type="checkbox" name="tagged_usernames[]" value="{{ $person->username }}" class="rounded">
                                {{ '@'.$person->username }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
            <textarea name="notes" rows="2" placeholder="Notes" class="input-field resize-none"></textarea>
            <button type="submit" class="btn-primary w-full">Save event</button>
        </form>
    </div>

    <div class="segment-control mb-3">
        <a href="{{ $params($date, 'month') }}" class="{{ $view === 'month' ? 'active' : '' }}">Month</a>
        <a href="{{ $params($date, 'week') }}" class="{{ $view === 'week' ? 'active' : '' }}">Week</a>
        <a href="{{ $params($date, 'day') }}" class="{{ $view === 'day' ? 'active' : '' }}">Day</a>
    </div>

    @if ($view === 'month')
        <div class="mb-1 grid grid-cols-7 gap-0.5 text-center font-sans text-[10px] font-bold uppercase tracking-wide text-garden-muted">
            @foreach (['S','M','T','W','T','F','S'] as $d)<div>{{ $d }}</div>@endforeach
        </div>
        <div class="calendar-grid-month mb-3">
            @php $day = $rangeStart->copy(); @endphp
            @while ($day <= $rangeEnd)
                @php
                    $key = $day->toDateString();
                    $dayOcc = $occurrences->get($key, collect());
                @endphp
                <a href="{{ $params($day, 'day') }}" class="calendar-day-cell {{ !$day->isSameMonth($date) ? 'other-month' : '' }} {{ $day->isToday() ? 'is-today' : '' }}">
                    <span class="text-right font-sans text-xs font-semibold">{{ $day->day }}</span>
                    <div class="min-h-0 flex-1 overflow-hidden">
                        @foreach ($dayOcc->take(4) as $occ)
                            @php $ev = $occ['source']; $c = $eventColor($ev->user_id); @endphp
                            <div class="calendar-event-pill" style="{{ \App\Services\CircleColorService::pillStyle($c) }}">
                                {{ $ev->title }}
                            </div>
                        @endforeach
                    </div>
                </a>
                @php $day->addDay(); @endphp
            @endwhile
        </div>
    @elseif ($view === 'week')
        <div class="calendar-grid-week mb-3">
            @php $day = $rangeStart->copy(); @endphp
            @while ($day <= $rangeEnd)
                @php $key = $day->toDateString(); $dayOcc = $occurrences->get($key, collect()); @endphp
                <a href="{{ $params($day, 'day') }}" class="calendar-week-cell {{ $day->isToday() ? 'is-today' : '' }}">
                    <span class="font-sans text-xs font-bold uppercase text-garden-muted">{{ $day->format('D') }}</span>
                    <span class="font-title text-lg font-bold">{{ $day->day }}</span>
                    <div class="mt-1 min-h-0 flex-1 space-y-1 overflow-y-auto">
                        @forelse ($dayOcc as $occ)
                            @php
                                $ev = $occ['source'];
                                $c = $eventColor($ev->user_id);
                                $timeLabel = $occ['occurrence_at']->format('g:i');
                                if ($occ['ends_at']) {
                                    $timeLabel .= '–'.$occ['ends_at']->format('g:i');
                                }
                            @endphp
                            <div class="calendar-event-pill" style="{{ \App\Services\CircleColorService::pillStyle($c) }}">
                                {{ $timeLabel }} {{ $ev->title }}
                            </div>
                        @empty
                            <span class="font-sans text-[10px] text-garden-muted/60">—</span>
                        @endforelse
                    </div>
                </a>
                @php $day->addDay(); @endphp
            @endwhile
        </div>
    @else
        <div class="calendar-day-view mb-3">
            @php $key = $date->toDateString(); $dayOcc = $occurrences->get($key, collect()); @endphp
            @forelse ($dayOcc as $occ)
                @php
                    $ev = $occ['source'];
                    $c = $eventColor($ev->user_id);
                    $isMine = $ev->user_id === auth()->id();
                    $isTagged = $ev->taggedUsers->contains('id', auth()->id());
                    $timeLabel = $occ['occurrence_at']->format('g:i A');
                    if ($occ['ends_at']) {
                        $timeLabel .= ' – '.$occ['ends_at']->format('g:i A');
                    }
                    $taggedUsernames = $ev->taggedUsers->pluck('username')->join(',');
                @endphp
                <div
                    class="calendar-event-block {{ $isMine ? 'cursor-pointer' : '' }}"
                    style="border-left: 4px solid {{ $c['border'] }}"
                    @if($isMine)
                        data-calendar-event
                        data-update-url="{{ route('calendar.update', $ev) }}"
                        data-event-title="{{ e($ev->title) }}"
                        data-event-notes="{{ e($ev->notes ?? '') }}"
                        data-event-starts="{{ $ev->starts_at->format('Y-m-d\TH:i') }}"
                        data-event-ends="{{ $ev->ends_at?->format('Y-m-d\TH:i') ?? '' }}"
                        data-event-recurrence="{{ $ev->recurrence }}"
                        data-event-recurrence-until="{{ $ev->recurrence_until?->toDateString() ?? '' }}"
                        data-event-tagged="{{ $taggedUsernames }}"
                    @endif
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="font-title text-lg font-bold text-garden-text">{{ $ev->title }}</p>
                            <p class="font-sans text-sm text-garden-muted">
                                {{ $timeLabel }}
                                @if($ev->recurrence !== 'none') · ↻ {{ $ev->recurrence }} @endif
                            </p>
                            <p class="owner-badge mt-1" style="color: {{ $c['text'] }}">
                                <span class="user-legend-dot inline-block" style="background: {{ $c['border'] }}"></span>
                                {{ $c['is_self'] ? 'Your event' : '@'.$c['username'] }}
                            </p>
                            @if ($isTagged && !$isMine)
                                <p class="mt-0.5 font-sans text-xs font-medium text-garden-accent">Tagged you</p>
                            @endif
                            @if ($ev->taggedUsers->isNotEmpty() && $isMine)
                                <p class="mt-0.5 font-sans text-xs text-garden-muted">
                                    Tagged: {{ $ev->taggedUsers->map(fn ($u) => '@'.$u->username)->join(', ') }}
                                </p>
                            @endif
                            @if ($isMine)
                                <p class="mt-1 font-sans text-xs font-medium text-garden-accent">Tap to edit</p>
                            @endif
                        </div>
                        @if ($isMine)
                            <form action="{{ route('calendar.destroy', $ev) }}" method="POST" onclick="event.stopPropagation()">
                                @csrf @method('DELETE')
                                <button type="submit" class="font-sans text-xs text-red-600">Remove</button>
                            </form>
                        @endif
                    </div>
                    @if ($ev->notes)
                        <p class="mt-2 font-sans text-sm text-garden-muted">{{ $ev->notes }}</p>
                    @endif
                </div>
            @empty
                <div class="flex flex-1 items-center justify-center py-12">
                    <p class="font-sans text-base text-garden-muted">No events this day</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
@endsection

@push('modals')
<div id="calendar-event-modal" class="modal-backdrop fixed inset-0 z-50 bg-garden-deep/50">
    <div class="modal-fullscreen fixed inset-0 flex flex-col bg-white pt-safe">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h2 class="font-title text-2xl font-bold text-garden-text">Edit event</h2>
            <button id="calendar-modal-close" type="button" class="font-sans text-lg font-medium text-garden-accent">Cancel</button>
        </div>
        <form id="calendar-event-form" method="POST" class="form-stack flex flex-1 flex-col overflow-y-auto px-4 py-5">
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <input type="text" id="calendar-event-title" name="title" required class="input-field">
            <input type="datetime-local" id="calendar-event-starts" name="starts_at" required class="input-field">
            <input type="datetime-local" id="calendar-event-ends" name="ends_at" class="input-field">
            <select id="calendar-event-recurrence" name="recurrence" class="input-field">
                <option value="none">Does not repeat</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
            <input type="date" id="calendar-event-recurrence-until" name="recurrence_until" class="input-field">
            @if ($connections->isNotEmpty())
                <div id="calendar-event-tags">
                    <p class="field-label">Tag people</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($connections as $person)
                            <label class="checkbox-chip">
                                <input type="checkbox" name="tagged_usernames[]" value="{{ $person->username }}" class="calendar-tag-checkbox rounded">
                                {{ '@'.$person->username }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
            <textarea id="calendar-event-notes" name="notes" rows="2" class="input-field resize-none"></textarea>
            <button type="submit" class="btn-primary mt-auto w-full">Save changes</button>
        </form>
    </div>
</div>
@endpush
