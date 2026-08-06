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
                $user = auth()->user();
                $view->with([
                    'treeEmoji' => $user->tree_emoji ?? '🌳',
                    'treeSize' => $user->treeFontSize(),
                    'unreadNotifications' => $user->unreadNotifications()->count(),
                ]);
            }
        });
    }
}
