<?php

namespace App\Providers;

use App\Services\WeatherService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $view->with('weather', app(WeatherService::class)->current());

            if (auth()->check()) {
                auth()->user()->gardenFlowers()->where('expires_at', '<=', now())->delete();

                $view->with('flowers', auth()->user()->gardenFlowers()->active()->get());
            }
        });
    }
}
