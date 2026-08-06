<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /** Northampton, Hampshire County, Massachusetts */
    private const LATITUDE = 42.3251;

    private const LONGITUDE = -72.6412;

    private const LOCATION = 'Hampshire County, MA';

    private const USER_AGENT = 'Tend/1.0 (https://github.com/jemarple1/tasks)';

    public function current(): array
    {
        return Cache::remember('weather.hampshire.current', now()->addMinutes(15), function () {
            $nws = $this->currentFromNws();

            if ($nws !== null) {
                return $nws;
            }

            return $this->currentFromOpenMeteo();
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function forecast(): array
    {
        return Cache::remember('weather.hampshire.forecast', now()->addMinutes(30), function () {
            $nws = $this->forecastFromNws();

            if ($nws !== null) {
                return $nws;
            }

            return $this->forecastFromOpenMeteo();
        });
    }

    private function currentFromNws(): ?array
    {
        try {
            $endpoints = $this->nwsEndpoints();

            if ($endpoints === null) {
                return null;
            }

            $hourly = Http::timeout(6)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($endpoints['hourly'])
                ->json('properties.periods', []);

            $now = Carbon::now('America/New_York');
            $period = collect($hourly)->first(function (array $p) use ($now) {
                $start = Carbon::parse($p['startTime']);

                return $start->lte($now) && $start->copy()->addHour()->gt($now);
            }) ?? ($hourly[0] ?? null);

            if ($period === null) {
                return null;
            }

            $temperature = $this->observedTemperatureF() ?? ($period['temperature'] ?? null);
            $isDay = (bool) ($period['isDaytime'] ?? true);
            $palette = $this->mapFromNwsText($period['shortForecast'] ?? '', $isDay);

            return array_merge($palette, [
                'temperature' => $temperature !== null ? round($temperature) : null,
                'location' => self::LOCATION,
                'short_forecast' => $period['shortForecast'] ?? $palette['label'],
                'source' => 'National Weather Service',
                'fetched_at' => now('America/New_York')->format('g:i A'),
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<int, array<string, mixed>>|null */
    private function forecastFromNws(): ?array
    {
        try {
            $endpoints = $this->nwsEndpoints();

            if ($endpoints === null) {
                return null;
            }

            $periods = Http::timeout(6)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($endpoints['forecast'])
                ->json('properties.periods', []);

            if (empty($periods)) {
                return null;
            }

            $days = [];

            for ($i = 0; $i < count($periods); $i += 2) {
                $dayPeriod = $periods[$i];
                $nightPeriod = $periods[$i + 1] ?? null;
                $mapped = $this->mapFromNwsText($dayPeriod['shortForecast'] ?? '', true);

                $high = $dayPeriod['isDaytime'] ? $dayPeriod['temperature'] : null;
                $low = $nightPeriod && ! $nightPeriod['isDaytime'] ? $nightPeriod['temperature'] : null;

                if ($high === null && $nightPeriod) {
                    $high = $nightPeriod['temperature'];
                }

                $days[] = [
                    'name' => $dayPeriod['name'],
                    'date' => Carbon::parse($dayPeriod['startTime'])->toDateString(),
                    'high' => $high,
                    'low' => $low,
                    'emoji' => $mapped['emoji'],
                    'label' => $dayPeriod['shortForecast'],
                ];

                if (count($days) >= 7) {
                    break;
                }
            }

            return $days;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{forecast: string, hourly: string, stations: string}|null */
    private function nwsEndpoints(): ?array
    {
        return Cache::remember('weather.nws.endpoints', now()->addDays(7), function () {
            $response = Http::timeout(6)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get('https://api.weather.gov/points/'.self::LATITUDE.','.self::LONGITUDE);

            if (! $response->successful()) {
                return null;
            }

            $props = $response->json('properties', []);

            if (empty($props['forecast']) || empty($props['forecastHourly'])) {
                return null;
            }

            return [
                'forecast' => $props['forecast'],
                'hourly' => $props['forecastHourly'],
                'stations' => $props['observationStations'] ?? '',
            ];
        });
    }

    private function observedTemperatureF(): ?float
    {
        try {
            $endpoints = $this->nwsEndpoints();

            if ($endpoints === null || empty($endpoints['stations'])) {
                return null;
            }

            $stationList = Http::timeout(6)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($endpoints['stations'])
                ->json('features.0.id');

            if (! $stationList) {
                return null;
            }

            $celsius = Http::timeout(6)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($stationList.'/observations/latest')
                ->json('properties.temperature.value');

            if ($celsius === null) {
                return null;
            }

            return ($celsius * 9 / 5) + 32;
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapFromNwsText(string $text, bool $isDay): array
    {
        $t = strtolower($text);

        if (str_contains($t, 'thunder') || str_contains($t, 'storm') || str_contains($t, 'tornado')) {
            return $this->palette('⛈', '#4a5568', '#718096', '#a0aec0', '#718096', 'Storms possible');
        }

        if (str_contains($t, 'snow') || str_contains($t, 'blizzard') || str_contains($t, 'sleet')) {
            return $this->palette('❄️', '#c8d6e5', '#dde7f0', '#eef3f8', '#dde7f0', 'Snowy');
        }

        if (str_contains($t, 'rain') || str_contains($t, 'shower') || str_contains($t, 'drizzle')) {
            if (str_contains($t, 'chance') && (str_contains($t, 'sun') || str_contains($t, 'partly'))) {
                return $this->palette('🌦', '#7eb8dc', '#a8d4ef', '#d4ecf9', '#a8d4ef', 'Partly sunny, showers possible');
            }

            return $this->palette('🌧', '#5b7c99', '#89a7c2', '#b8cfe0', '#89a7c2', 'Rainy');
        }

        if (str_contains($t, 'fog') || str_contains($t, 'haze')) {
            return $this->palette('🌫', '#b0bec5', '#cfd8dc', '#eceff1', '#cfd8dc', 'Foggy');
        }

        if (str_contains($t, 'sunny') && ! str_contains($t, 'partly') && ! str_contains($t, 'mostly')) {
            return $this->palette('☀️', '#6ec4e8', '#9dd6f3', '#cce9fa', '#9dd6f3', 'Sunny');
        }

        if (str_contains($t, 'mostly sunny')) {
            return $this->palette('🌤', '#7ec8e8', '#a8daf0', '#d4ecf9', '#a8daf0', 'Mostly sunny');
        }

        if (str_contains($t, 'partly sunny') || str_contains($t, 'partly cloudy')) {
            return $this->palette('⛅', '#8ecae6', '#b8dff5', '#e0f2fe', '#b8dff5', 'Partly sunny');
        }

        if (str_contains($t, 'mostly cloudy')) {
            return $this->palette('🌥', '#a8b8c8', '#c5d4e0', '#e2ebf2', '#c5d4e0', 'Mostly cloudy');
        }

        if (str_contains($t, 'cloudy') || str_contains($t, 'overcast')) {
            return $this->palette('☁️', '#94a3b8', '#cbd5e1', '#e2e8f0', '#cbd5e1', 'Cloudy');
        }

        if (str_contains($t, 'clear')) {
            return $isDay
                ? $this->palette('☀️', '#6ec4e8', '#9dd6f3', '#cce9fa', '#9dd6f3', 'Clear')
                : $this->palette('🌙', '#1e3a5f', '#2d4a6f', '#3d5a80', '#2d4a6f', 'Clear night');
        }

        return $isDay
            ? $this->palette('🌤', '#8ecae6', '#b8dff5', '#e0f2fe', '#b8dff5', 'Fair')
            : $this->palette('🌙', '#1e3a5f', '#2d4a6f', '#3d5a80', '#2d4a6f', 'Fair night');
    }

    /** @return array{emoji: string, from: string, via: string, to: string, theme: string, label: string} */
    private function palette(string $emoji, string $from, string $via, string $to, string $theme, string $label): array
    {
        return compact('emoji', 'from', 'via', 'to', 'theme', 'label');
    }

    private function currentFromOpenMeteo(): array
    {
        try {
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => self::LATITUDE,
                'longitude' => self::LONGITUDE,
                'current' => 'weather_code,is_day,temperature_2m,cloud_cover,precipitation,direct_radiation',
                'temperature_unit' => 'fahrenheit',
                'timezone' => 'America/New_York',
            ]);

            if ($response->successful()) {
                $current = $response->json('current', []);
                $code = (int) ($current['weather_code'] ?? 0);
                $isDay = (bool) ($current['is_day'] ?? true);
                $cloudCover = $current['cloud_cover'] ?? null;
                $precipitation = $current['precipitation'] ?? 0;
                $radiation = $current['direct_radiation'] ?? null;

                $code = $this->refineWeatherCode($code, $isDay, $cloudCover, $precipitation, $radiation);

                return array_merge(
                    $this->mapWeatherCode($code, $isDay),
                    [
                        'temperature' => isset($current['temperature_2m']) ? round($current['temperature_2m']) : null,
                        'location' => self::LOCATION,
                        'short_forecast' => $this->mapWeatherCode($code, $isDay)['label'],
                        'source' => 'Open-Meteo (fallback)',
                        'fetched_at' => now('America/New_York')->format('g:i A'),
                    ],
                );
            }
        } catch (\Throwable) {
            //
        }

        return array_merge($this->mapFromNwsText('partly sunny', true), [
            'temperature' => null,
            'location' => self::LOCATION,
            'short_forecast' => 'Weather unavailable',
            'source' => 'Default',
            'fetched_at' => now('America/New_York')->format('g:i A'),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function forecastFromOpenMeteo(): array
    {
        try {
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => self::LATITUDE,
                'longitude' => self::LONGITUDE,
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
                'temperature_unit' => 'fahrenheit',
                'timezone' => 'America/New_York',
                'forecast_days' => 7,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $daily = $response->json('daily', []);
            $days = [];

            foreach ($daily['time'] ?? [] as $i => $date) {
                $code = (int) ($daily['weather_code'][$i] ?? 1);
                $mapped = $this->mapWeatherCode($code, true);

                $days[] = [
                    'name' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'high' => isset($daily['temperature_2m_max'][$i]) ? round($daily['temperature_2m_max'][$i]) : null,
                    'low' => isset($daily['temperature_2m_min'][$i]) ? round($daily['temperature_2m_min'][$i]) : null,
                    'emoji' => $mapped['emoji'],
                    'label' => $mapped['label'],
                ];
            }

            return $days;
        } catch (\Throwable) {
            return [];
        }
    }

    private function refineWeatherCode(int $code, bool $isDay, ?float $cloudCover, float $precipitation, ?float $radiation): int
    {
        if ($precipitation > 0.1) {
            return $code >= 51 ? $code : 61;
        }

        if (! $isDay) {
            return $code;
        }

        if ($radiation !== null && $radiation >= 300) {
            return 0;
        }

        if ($radiation !== null && $radiation >= 120) {
            return min($code, 2);
        }

        if ($cloudCover !== null) {
            if ($cloudCover <= 15) {
                return 0;
            }
            if ($cloudCover <= 35) {
                return 1;
            }
            if ($cloudCover <= 60) {
                return 2;
            }
        }

        if (in_array($code, [2, 3], true) && $radiation !== null && $radiation > 0) {
            return 2;
        }

        return $code;
    }

    /** @return array{emoji: string, from: string, via: string, to: string, theme: string, label: string} */
    private function mapWeatherCode(int $code, bool $isDay): array
    {
        return match (true) {
            in_array($code, [0]) => $this->palette(
                $isDay ? '☀️' : '🌙',
                $isDay ? '#6ec4e8' : '#1e3a5f',
                $isDay ? '#9dd6f3' : '#2d4a6f',
                $isDay ? '#cce9fa' : '#3d5a80',
                $isDay ? '#9dd6f3' : '#2d4a6f',
                $isDay ? 'Clear skies' : 'Clear night',
            ),
            $code === 1 => $this->palette('🌤', '#7ec8e8', '#a8daf0', '#d4ecf9', '#a8daf0', 'Mostly clear'),
            $code === 2 => $this->palette('⛅', '#8ecae6', '#b8dff5', '#e0f2fe', '#b8dff5', 'Partly cloudy'),
            $code === 3 => $this->palette('☁️', '#94a3b8', '#cbd5e1', '#e2e8f0', '#cbd5e1', 'Overcast'),
            in_array($code, [45, 48]) => $this->palette('🌫', '#b0bec5', '#cfd8dc', '#eceff1', '#cfd8dc', 'Foggy'),
            in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82]) => $this->palette('🌧', '#5b7c99', '#89a7c2', '#b8cfe0', '#89a7c2', 'Rainy'),
            in_array($code, [71, 73, 75, 77, 85, 86]) => $this->palette('❄️', '#c8d6e5', '#dde7f0', '#eef3f8', '#dde7f0', 'Snowy'),
            in_array($code, [95, 96, 99]) => $this->palette('⛈', '#4a5568', '#718096', '#a0aec0', '#718096', 'Stormy'),
            default => $this->palette('🌤', '#8ecae6', '#b8dff5', '#e0f2fe', '#b8dff5', 'Fair'),
        };
    }
}
