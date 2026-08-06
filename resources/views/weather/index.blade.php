@extends('layouts.app')

@section('title', 'Weather — Tend')

@section('content')
    <header class="mb-4 flex items-center gap-2 pt-1">
        <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-title text-2xl font-bold text-garden-text">Weather</h1>
    </header>

    <section class="mb-5 rounded-2xl border-2 border-white/70 bg-white/80 p-5 shadow-sm backdrop-blur-sm">
        <p class="font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">{{ $current['location'] ?? 'Hampshire County, MA' }}</p>
        <div class="mt-2 flex items-center gap-4">
            <span class="text-6xl" role="img" aria-label="{{ $current['label'] ?? 'Weather' }}">{{ $current['emoji'] ?? '🌤' }}</span>
            <div>
                @if(isset($current['temperature']))
                    <p class="font-title text-5xl font-bold leading-none text-garden-text">{{ $current['temperature'] }}°</p>
                @endif
                <p class="mt-1 font-title text-lg font-semibold text-garden-text">{{ $current['short_forecast'] ?? $current['label'] ?? '—' }}</p>
            </div>
        </div>
        <p class="mt-3 font-sans text-xs text-garden-muted">
            Updated {{ $current['fetched_at'] ?? 'recently' }} · {{ $current['source'] ?? 'Weather service' }}
        </p>
    </section>

    <section>
        <h2 class="mb-3 font-title text-xl font-bold text-garden-text">7-day forecast</h2>
        <div class="space-y-2">
            @forelse ($forecast as $day)
                <div class="flex items-center gap-3 rounded-xl border-2 border-white/60 bg-white/70 px-4 py-3">
                    <span class="text-2xl" role="img" aria-hidden="true">{{ $day['emoji'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="font-title text-base font-semibold text-garden-text">{{ $day['name'] }}</p>
                        <p class="truncate font-sans text-sm text-garden-muted">{{ $day['label'] }}</p>
                    </div>
                    <div class="shrink-0 text-right font-sans text-sm font-semibold text-garden-text">
                        @if(isset($day['high']))
                            <span>{{ $day['high'] }}°</span>
                        @endif
                        @if(isset($day['low']))
                            <span class="text-garden-muted"> / {{ $day['low'] }}°</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="py-8 text-center font-sans text-garden-muted">Forecast unavailable right now.</p>
            @endforelse
        </div>
    </section>
@endsection
