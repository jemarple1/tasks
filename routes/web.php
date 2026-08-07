<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\GroceryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('/tasks/{task}/snooze', [TaskController::class, 'snooze'])->name('tasks.snooze');
    Route::patch('/tasks/{task}/refresh', [TaskController::class, 'refreshExpiry'])->name('tasks.refresh');
    Route::get('/complete', [TaskController::class, 'completeIndex'])->name('tasks.complete.index');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::patch('/calendar/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    Route::get('/grocery', [GroceryController::class, 'index'])->name('grocery.index');
    Route::post('/grocery', [GroceryController::class, 'store'])->name('grocery.store');
    Route::patch('/grocery/{item}/complete', [GroceryController::class, 'complete'])->name('grocery.complete');
    Route::delete('/grocery/{item}', [GroceryController::class, 'destroy'])->name('grocery.destroy');

    Route::redirect('/garden', '/settings');

    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/connections', [ConnectionController::class, 'store'])->name('connections.store');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
