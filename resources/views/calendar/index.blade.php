@extends('layouts.app')

@section('title', 'Calendar — Tend')

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
    <header class="page-header flex items-center justify-between gap-2">
        @include('partials.weather-badge')
        <div class="flex items-center gap-1.5">
            <a href="{{ $params($prevDate) }}" class="nav-btn" aria-label="Previous">
                <x-icon name="chevron-left" />
            </a>
            <a href="{{ $params(now()) }}" class="nav-btn" aria-label="Today">
                <x-icon name="clock" />
            </a>
            <a href="{{ $params($nextDate) }}" class="nav-btn" aria-label="Next">
                <x-icon name="chevron-right" />
            </a>
        </div>
    </header>

    <div class="mb-3">
        <h1 class="page-title">{{ $label }}</h1>
        @if ($connections->isNotEmpty())
            <p class="page-subtitle">Shared with {{ $connections->count() }} {{ Str::plural('person', $connections->count()) }}</p>
        @endif
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

    <button type="button" id="calendar-add-toggle" class="add-panel-toggle mb-3">
        <span class="flex items-center gap-2">
            <x-icon name="plus" />
            Add event
        </span>
        <x-icon name="chevron-down" class="h-4 w-4 opacity-60" />
    </button>

    <div id="calendar-add-panel" class="surface-card form-stack mb-4 hidden p-4">
        <form action="{{ route('calendar.store') }}" method="POST" class="space-y-3">
            @csrf
            <input type="text" name="title" placeholder="Event title" required class="input-field">
            <input type="datetime-local" name="starts_at" required value="{{ $date->format('Y-m-d\TH:i') }}" class="input-field">
            <input type="datetime-local" name="ends_at" class="input-field">
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
        <div class="calendar-weekday-row">
            @foreach (['S','M','T','W','T','F','S'] as $d)<div>{{ $d }}</div>@endforeach
        </div>
        <div class="calendar-grid-month">
            @php $day = $rangeStart->copy(); @endphp
            @while ($day <= $rangeEnd)
                @php
                    $key = $day->toDateString();
                    $dayOcc = $occurrences->get($key, collect());
                @endphp
                <a href="{{ $params($day, 'day') }}" class="calendar-day-cell {{ !$day->isSameMonth($date) ? 'other-month' : '' }} {{ $day->isToday() ? 'is-today' : '' }}">
                    <span class="calendar-day-number">{{ $day->day }}</span>
                    <span class="calendar-day-dots">
                        @foreach ($dayOcc->take(4) as $occ)
                            <span class="calendar-dot" style="background: {{ $eventColor($occ['source']->user_id)['border'] }}"></span>
                        @endforeach
                    </span>
                </a>
                @php $day->addDay(); @endphp
            @endwhile
        </div>
    @elseif ($view === 'week')
        <div class="week-agenda">
            @php $day = $rangeStart->copy(); @endphp
            @while ($day <= $rangeEnd)
                @php $key = $day->toDateString(); $dayOcc = $occurrences->get($key, collect()); @endphp
                <div class="week-agenda-row {{ $day->isToday() ? 'is-today' : '' }}">
                    <a href="{{ $params($day, 'day') }}" class="week-agenda-date" aria-label="{{ $day->format('l, F j') }}">
                        <span class="week-agenda-dow">{{ $day->format('D') }}</span>
                        <span class="week-agenda-num">{{ $day->day }}</span>
                    </a>
                    <div class="week-agenda-events">
                        @forelse ($dayOcc as $occ)
                            @php
                                $ev = $occ['source'];
                                $c = $eventColor($ev->user_id);
                            @endphp
                            <a href="{{ $params($day, 'day') }}" class="week-agenda-event" style="background: {{ $c['bg'] }}; color: {{ $c['text'] }}; border-left: 3px solid {{ $c['border'] }}">
                                <span class="week-agenda-time">{{ $occ['occurrence_at']->format('g:i A') }}</span>
                                <span class="week-agenda-title">{{ $ev->title }}</span>
                            </a>
                        @empty
                            <span class="week-agenda-empty">No events</span>
                        @endforelse
                    </div>
                </div>
                @php $day->addDay(); @endphp
            @endwhile
        </div>
    @else
        <div class="calendar-day-view">
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
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-sans text-base font-semibold tracking-tight text-garden-text">{{ $ev->title }}</p>
                            <p class="mt-1 flex flex-wrap items-center gap-x-1.5 font-sans text-[13px] text-garden-muted">
                                <x-icon name="clock" class="h-3.5 w-3.5" />
                                {{ $timeLabel }}
                                @if($ev->recurrence !== 'none')
                                    <span class="task-meta-dot">•</span>
                                    <x-icon name="repeat" class="h-3.5 w-3.5" /> {{ $ev->recurrence }}
                                @endif
                            </p>
                            <p class="owner-badge mt-1.5" style="color: {{ $c['text'] }}">
                                <span class="user-legend-dot" style="background: {{ $c['border'] }}"></span>
                                {{ $c['is_self'] ? 'You' : '@'.$c['username'] }}
                                @if ($isTagged && !$isMine)
                                    <span class="ml-1 font-medium text-garden-accent">· tagged you</span>
                                @endif
                            </p>
                            @if ($ev->taggedUsers->isNotEmpty() && $isMine)
                                <p class="mt-1 font-sans text-xs text-garden-muted">
                                    Tagged {{ $ev->taggedUsers->map(fn ($u) => '@'.$u->username)->join(', ') }}
                                </p>
                            @endif
                        </div>
                        @if ($isMine)
                            <form action="{{ route('calendar.destroy', $ev) }}" method="POST" onclick="event.stopPropagation()">
                                @csrf @method('DELETE')
                                <button type="submit" class="nav-btn h-8 w-8 text-garden-muted" aria-label="Delete event">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                    @if ($ev->notes)
                        <p class="mt-2 font-sans text-[13px] leading-relaxed text-garden-muted">{{ $ev->notes }}</p>
                    @endif
                </div>
            @empty
                <div class="py-14 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white/70 text-garden-muted">
                        <x-icon name="calendar" class="h-7 w-7" />
                    </div>
                    <p class="empty-title">Nothing scheduled</p>
                    <p class="empty-body">Add an event to fill this day</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
@endsection

@push('modals')
<div id="calendar-event-modal" class="modal-backdrop fixed inset-0 z-50 bg-garden-deep/50">
    <div class="modal-fullscreen fixed inset-0 flex flex-col bg-white pt-safe">
        <div class="modal-header">
            <h2 class="modal-title">Edit event</h2>
            <button id="calendar-modal-close" type="button" class="font-sans text-[15px] font-semibold text-garden-accent">Cancel</button>
        </div>
        <form id="calendar-event-form" method="POST" class="form-stack flex flex-1 flex-col overflow-y-auto px-4 py-5">
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <div>
                <label for="calendar-event-title" class="field-label">Title</label>
                <input type="text" id="calendar-event-title" name="title" required class="input-field">
            </div>
            <div>
                <label for="calendar-event-starts" class="field-label">Starts</label>
                <input type="datetime-local" id="calendar-event-starts" name="starts_at" required class="input-field">
            </div>
            <div>
                <label for="calendar-event-ends" class="field-label">Ends</label>
                <input type="datetime-local" id="calendar-event-ends" name="ends_at" class="input-field">
            </div>
            <div>
                <label for="calendar-event-recurrence" class="field-label">Repeat</label>
                <select id="calendar-event-recurrence" name="recurrence" class="input-field">
                    <option value="none">Does not repeat</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div>
                <label for="calendar-event-recurrence-until" class="field-label">Repeat until</label>
                <input type="date" id="calendar-event-recurrence-until" name="recurrence_until" class="input-field">
            </div>
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
            <div>
                <label for="calendar-event-notes" class="field-label">Notes</label>
                <textarea id="calendar-event-notes" name="notes" rows="3" class="input-field resize-none"></textarea>
            </div>
            <button type="submit" class="btn-primary mt-auto w-full">Save changes</button>
        </form>
    </div>
</div>
@endpush
