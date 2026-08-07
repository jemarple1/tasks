@extends('layouts.app')

@section('title', 'Weather — Tend')

@section('content')
    <header class="page-header flex items-center gap-3">
        <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
            <x-icon name="chevron-left" />
        </a>
        <h1 class="page-title">Weather</h1>
    </header>

    <section class="surface-card mb-5 p-5">
        <p class="section-title">{{ $current['location'] ?? 'Hampshire County, MA' }}</p>
        <div class="mt-3 flex items-center gap-4">
            <span class="text-6xl leading-none" role="img" aria-label="{{ $current['label'] ?? 'Weather' }}">{{ $current['emoji'] ?? '🌤' }}</span>
            <div class="min-w-0">
                @if(isset($current['temperature']))
                    <p class="font-sans text-5xl font-bold leading-none tracking-tighter text-garden-text">{{ $current['temperature'] }}°</p>
                @endif
                <p class="mt-1.5 font-sans text-[15px] font-medium text-garden-muted">{{ $current['short_forecast'] ?? $current['label'] ?? '—' }}</p>
            </div>
        </div>
        <p class="hint-text mt-4">
            Updated {{ $current['fetched_at'] ?? 'recently' }} · {{ $current['source'] ?? 'Weather service' }}
        </p>
    </section>

    <section>
        <h2 class="section-title mb-2.5">7-day forecast</h2>
        <div class="space-y-2">
            @forelse ($forecast as $day)
                <div class="surface-card flex items-center gap-3 px-4 py-3">
                    <span class="text-2xl leading-none" role="img" aria-hidden="true">{{ $day['emoji'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="font-sans text-[15px] font-semibold tracking-tight text-garden-text">{{ $day['name'] }}</p>
                        <p class="truncate font-sans text-[13px] text-garden-muted">{{ $day['label'] }}</p>
                    </div>
                    <div class="shrink-0 text-right font-sans text-[15px] font-semibold text-garden-text">
                        @if(isset($day['high']))
                            <span>{{ $day['high'] }}°</span>
                        @endif
                        @if(isset($day['low']))
                            <span class="font-medium text-garden-faint">/ {{ $day['low'] }}°</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="py-8 text-center font-sans text-[15px] text-garden-muted">Forecast unavailable right now.</p>
            @endforelse
        </div>
    </section>
@endsection
