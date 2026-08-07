<a href="{{ route('weather.index') }}" class="weather-badge" aria-label="Weather">
    <span class="weather-badge-emoji" role="img" aria-hidden="true">{{ $weather['emoji'] ?? '🌤' }}</span>
    @if(isset($weather['temperature']))
        <span class="weather-badge-temp">{{ $weather['temperature'] }}°</span>
    @endif
</a>
