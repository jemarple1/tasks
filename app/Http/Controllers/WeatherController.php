<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\View\View;

class WeatherController extends Controller
{
    public function index(WeatherService $weather): View
    {
        return view('weather.index', [
            'current' => $weather->current(),
            'forecast' => $weather->forecast(),
        ]);
    }
}
