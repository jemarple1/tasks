<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /** Northampton, Hampshire County, Massachusetts */
    private const LATITUDE = 42.3251;

    private const LONGITUDE = -72.6412;

    public function current(): array
    {
        return Cache::remember('weather.hampshire_ma', now()->addMinutes(30), function () {
            try {
                $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => self::LATITUDE,
                    'longitude' => self::LONGITUDE,
                    'current' => 'weather_code,is_day,temperature_2m',
                    'timezone' => 'America/New_York',
                ]);

                if ($response->successful()) {
                    $current = $response->json('current', []);

                    return $this->mapWeather(
                        (int) ($current['weather_code'] ?? 0),
                        (bool) ($current['is_day'] ?? true),
                        $current['temperature_2m'] ?? null,
                    );
                }
            } catch (\Throwable) {
                //
            }

            return $this->defaultWeather();
        });
    }

    private function mapWeather(int $code, bool $isDay, ?float $temperature): array
    {
        $palette = match (true) {
            in_array($code, [0]) => [
                'emoji' => $isDay ? '☀️' : '🌙',
                'from' => $isDay ? '#7ec8e8' : '#1e3a5f',
                'via' => $isDay ? '#a8daf0' : '#2d4a6f',
                'to' => $isDay ? '#d4ecf9' : '#3d5a80',
                'theme' => $isDay ? '#a8daf0' : '#2d4a6f',
                'label' => $isDay ? 'Clear skies' : 'Clear night',
            ],
            in_array($code, [1, 2]) => [
                'emoji' => '🌤',
                'from' => '#8ecae6',
                'via' => '#b8dff5',
                'to' => '#e0f2fe',
                'theme' => '#b8dff5',
                'label' => 'Mostly sunny',
            ],
            $code === 3 => [
                'emoji' => '☁️',
                'from' => '#94a3b8',
                'via' => '#cbd5e1',
                'to' => '#e2e8f0',
                'theme' => '#cbd5e1',
                'label' => 'Overcast',
            ],
            in_array($code, [45, 48]) => [
                'emoji' => '🌫',
                'from' => '#b0bec5',
                'via' => '#cfd8dc',
                'to' => '#eceff1',
                'theme' => '#cfd8dc',
                'label' => 'Foggy',
            ],
            in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82]) => [
                'emoji' => '🌧',
                'from' => '#5b7c99',
                'via' => '#89a7c2',
                'to' => '#b8cfe0',
                'theme' => '#89a7c2',
                'label' => 'Rainy',
            ],
            in_array($code, [71, 73, 75, 77, 85, 86]) => [
                'emoji' => '❄️',
                'from' => '#c8d6e5',
                'via' => '#dde7f0',
                'to' => '#eef3f8',
                'theme' => '#dde7f0',
                'label' => 'Snowy',
            ],
            in_array($code, [95, 96, 99]) => [
                'emoji' => '⛈',
                'from' => '#4a5568',
                'via' => '#718096',
                'to' => '#a0aec0',
                'theme' => '#718096',
                'label' => 'Stormy',
            ],
            default => [
                'emoji' => '🌤',
                'from' => '#8ecae6',
                'via' => '#b8dff5',
                'to' => '#e0f2fe',
                'theme' => '#b8dff5',
                'label' => 'Partly cloudy',
            ],
        };

        $palette['temperature'] = $temperature !== null ? round($temperature) : null;
        $palette['location'] = 'Hampshire County, MA';

        return $palette;
    }

    private function defaultWeather(): array
    {
        return $this->mapWeather(1, true, null);
    }
}
